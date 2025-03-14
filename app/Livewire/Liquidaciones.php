<?php

namespace App\Livewire;

use App\Models\FComprobanteSunat;
use App\Models\Movimiento;
use App\Models\MovimientoDetalle;
use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Liquidaciones extends Component
{
    public function render()
    {
        return view('livewire.liquidaciones');
    }

    public $fecha_fin;
    public $movimientos;
    public $movimiento_id;
    public $comprobantes;
    public $productos;
    public $view;
    public $regresa;
    public $por_anular;
    public $search_productos;
    public $search;
    public $detalles;

    public function mount()
    {
        $this->fecha_fin = Carbon::now();

        if ($this->fecha_fin->isMonday()) {
            $fecha_fin = $this->fecha_fin->subDays(2); // Agregar 2 días si es sábado
        } else {
            $fecha_fin = $this->fecha_fin->subDay(); // Agregar 1 día en otros casos
        }
        $this->fecha_fin = $fecha_fin->toDateString();
        $this->view = 'liquidaciones';
        $this->regresa = false;
        $this->por_anular = [];
        $this->detalles = [];
        $this->mount_propiedades();
    }

    public function mount_propiedades()
    {
        $this->movimientos = Movimiento::whereBetween('fecha_liquidacion', [$this->fecha_fin, $this->fecha_fin])->whereIn('estado', ['Por liquidar', 'liquidado'])->get();
    }

    public function updatedFechaFin()
    {
        $this->mount_propiedades();
    }

    public function liquidar($movimiento_id)
    {
        $this->view = 'liquidacion detalle';
        $this->movimientos = Movimiento::with(['conductor'])->where("id", $movimiento_id)->get();

        // Obtener detalles del movimiento
        $movimiento_detalles = MovimientoDetalle::where("movimiento_id", $movimiento_id)->get();
        $md_producto_ids = $movimiento_detalles->pluck('producto_id')->unique();

        // Obtener detalles del comprobante
        $comprobante_detalles = DB::table('f_comprobante_sunat_detalles')
            ->select(
                'f_comprobante_sunat_detalles.codProducto',
                DB::raw('COUNT(f_comprobante_sunat_detalles.f_comprobante_sunat_id) AS cantidad_comprobantes'),
                DB::raw('SUM(f_comprobante_sunat_detalles.cantidad) AS total_cantidad')
            )
            ->join('f_comprobante_sunats', 'f_comprobante_sunat_detalles.f_comprobante_sunat_id', '=', 'f_comprobante_sunats.id')
            ->where('f_comprobante_sunats.movimiento_id', $movimiento_id)
            ->where('f_comprobante_sunats.estado_reporte', true)
            ->groupBy('f_comprobante_sunat_detalles.codProducto')
            ->orderByRaw('CAST(f_comprobante_sunat_detalles.codProducto AS UNSIGNED)')
            ->get();

        $cd_producto_ids = $comprobante_detalles->pluck('codProducto')->unique();

        // Unir IDs de productos únicos
        $producto_ids = $cd_producto_ids->merge($md_producto_ids)->unique();

        // Obtener productos con datos mínimos
        $productos = DB::table('productos')->whereIn('id', $producto_ids)->get(['id', 'name', 'cantidad']);

        // Mapear datos para acceso rápido
        $movimientoMap = $movimiento_detalles->pluck('cantidad', 'producto_id');
        $cd_cantidad_comprobantesMap = $comprobante_detalles->pluck('cantidad_comprobantes', 'codProducto');
        $cd_total_cantidadMap = $comprobante_detalles->pluck('total_cantidad', 'codProducto');

        // Transformar productos agregando datos
        $this->productos = $productos->map(function ($producto) use ($movimientoMap, $cd_cantidad_comprobantesMap, $cd_total_cantidadMap) {
            $movimientoCantidad = $movimientoMap[$producto->id] ?? 0;
            $totalComprobantesCantidad = $cd_total_cantidadMap[$producto->id] ?? 0;

            list($cajas, $paquetes) = explode(".", number_format_punto2($movimientoCantidad));

            $producto->movimiento_cantidad_cajas = $movimientoCantidad;
            $producto->movimiento_cantidad_en_paquetes = ($cajas * $producto->cantidad) + $paquetes;
            $producto->cantidad_comprobantes = $cd_cantidad_comprobantesMap[$producto->id] ?? 0;

            $producto->comprobantes_cantidad_cajas = number_format_punto2(
                intdiv($totalComprobantesCantidad, $producto->cantidad) + ($totalComprobantesCantidad % $producto->cantidad) / 100
            );
            $producto->comprobantes_cantidad_paquetes = intval($totalComprobantesCantidad);

            $producto->diferencia_paquetes = $producto->movimiento_cantidad_en_paquetes - $producto->comprobantes_cantidad_paquetes;
            $producto->diferencia_cajas = number_format_punto2(intdiv($producto->diferencia_paquetes, $producto->cantidad) + ($producto->diferencia_paquetes % $producto->cantidad) / 100);

            return $producto;
        });
        $this->movimiento_id = $movimiento_id;
    }

    public function volver()
    {
        $this->view = 'liquidaciones';
        $this->mount_propiedades();
        unset($this->productos, $this->movimiento_id);
    }

    public function diferencias()
    {
        $this->view = 'liquidacion detalle';
        $this->regresa = true;
        $this->productos = $this->productos->filter(function ($producto) {
            return $producto->diferencia_paquetes != 0;
        });
    }

    public function regresar()
    {
        $this->view = 'liquidacion detalle';
        $this->regresa = false;
        $this->liquidar($this->movimiento_id);
        $this->por_anular = [];
    }

    public function liquidacion_comprobantes()
    {
        $this->view = 'liquidacion comprobantes';
        $this->regresa = true;
        $this->comprobantes = DB::table('f_comprobante_sunats')
            ->select(
                'f_comprobante_sunats.id',
                'f_comprobante_sunats.movimiento_id',
                'f_comprobante_sunats.tipoDoc_name',
                'f_comprobante_sunats.serie',
                'f_comprobante_sunats.correlativo',
                'f_comprobante_sunats.cliente_id',
                'f_comprobante_sunats.clientRazonSocial',
                'f_comprobante_sunats.mtoImpVenta',
                'f_comprobante_sunats.estado_reporte',
                'f_comprobante_sunats.fechaEmision',
            )
            ->where('f_comprobante_sunats.movimiento_id', $this->movimiento_id)
            ->whereNotIn('tipoDoc', ["07", "08"])
            ->get();
    }

    public function anular_cp($comprobante_id)
    {
        $this->por_anular[$comprobante_id] = false;

        // Buscar el comprobante y cambiar su estado
        if ($comprobante = $this->comprobantes->firstWhere('id', $comprobante_id)) {
            $comprobante->estado_reporte = false;
        }
    }

    public function desanular_cp($comprobante_id)
    {
        $this->por_anular[$comprobante_id] = true;

        // Buscar el comprobante y restaurar su estado
        if ($comprobante = $this->comprobantes->firstWhere('id', $comprobante_id)) {
            $comprobante->estado_reporte = true;
        }
    }

    public function guardar_anulados()
    {
        // Anular comprobantes
        $cp = FComprobanteSunat::whereIn('id', array_keys($this->por_anular))->get();
        $cp->each(function ($comprobante) {
            $comprobante->estado_reporte = $this->por_anular[$comprobante->id];
            $comprobante->save();
        });
        // Actualizar comprobantes
        $this->liquidacion_comprobantes();
    }

    public function agregar_ingreso()
    {
        $this->view = 'agregar ingreso';
        $this->regresa = true;
    }

    public function updatedSearch()
    {
        $lista_precio = 1;
        if (!$lista_precio) {
            return;
        }

        if (strlen($this->search) > 0) {
            $this->productos = Producto::withTrashed()->where(function ($query) {
                $query
                    ->where("name", "like", "%" . $this->search . "%")
                    ->orWhere("id", "like", "%" . $this->search . "%");
            })
                ->with([
                    "marca",
                    "listaPrecios" => function ($query) use ($lista_precio) {
                        $query->where("lista_precio_id", $lista_precio);
                    },
                    "almacenProductos" => function ($query) {
                        $query->whereIn("almacen_id", $this->almacenes->pluck("id"));
                    },
                ])
                ->take(15)
                ->get();
        } else {
            $this->productos = [];
        }
    }

    public function agregarProducto($producto_id)
    {
        if (!$this->lista_precio) {
            // Mostrar mensaje de error o alerta
            return;
        }

        $producto = Producto::withTrashed()->with([
            "listaPrecios" => function ($query) {
                $query->where("lista_precio_id", $this->lista_precio);
            },
        ])->find($producto_id);

        if (!$producto) {
            return;
        }

        // Verificar si el producto ya existe en el detalle
        $existe = collect($this->detalles)->first(function ($detalle) use ($producto_id) {
            return $detalle["producto_id"] === $producto_id;
        });

        if (!$existe) {
            $precio = $producto->listaPrecios->first()?->pivot?->precio ?? 0;
            $cantidad = 1;

            //if ($precio > 0) {
            $this->detalles[] = [
                "producto_id" => $producto->id,
                "producto_name" => $producto->name,
                "cantidad" => number_format($cantidad, 2, '.', ''),
                "codigo" => $producto->id,
                "precio_venta_unitario" => $precio,
                "precio_venta_total" => $precio * $cantidad,
                "costo_unitario" => $precio,
                "costo_total" => $precio * $cantidad,
                "empleado_id" => $this->user->user_empleado->empleado_id,
            ];

            usort($this->detalles, function ($a, $b) {
                return $b['producto_id'] <=> $a['producto_id'];
            });
            //}
        }

        // Limpiar búsqueda
        $this->search = "";
        $this->productos = [];
    }

    public function eliminarDetalle($index)
    {
        unset($this->detalles[$index]);
        $this->detalles = array_values($this->detalles);
    }
}
