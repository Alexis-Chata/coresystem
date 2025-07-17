<?php

namespace App\Livewire;

use App\Models\Almacen;
use App\Models\FComprobanteSunat;
use App\Models\Movimiento;
use App\Models\MovimientoDetalle;
use App\Models\Producto;
use App\Models\TipoMovimiento;
use App\Traits\StockTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use ParseError;

class Liquidaciones extends Component
{
    public function render()
    {
        $this->dispatch('DataTable-initialize');
        return view('livewire.liquidaciones');
    }

    use StockTrait;

    public $fecha_fin;
    public $movimientos;
    public $movimiento_id;
    public $comprobantes;
    public $productos;
    public $view;
    public $regresa;
    public $por_anular;
    public $listado_productos = [];
    public $search_productos;
    public $search;
    public $detalles;
    public $almacenes;
    public $tipo_movimiento_id;
    public $tipo_movimiento_name;

    protected $listeners = ['recargar-productos' => 'cargarProductos'];

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
        $this->cargarProductos();
    }

    public function mount_propiedades()
    {
        $this->movimientos = Movimiento::with('pedidos')->whereBetween('fecha_liquidacion', [$this->fecha_fin, $this->fecha_fin])->whereIn('estado', ['Por liquidar', 'liquidado'])->get();
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
        $movimientos_extras = Movimiento::with(['conductor'])->where("nro_doc_liquidacion", $movimiento_id)->whereNotIn('id', [$movimiento_id])->get();
        //dd($movimientos_extras->pluck('id'));
        $movimiento_detalles_extras = MovimientoDetalle::whereIn("movimiento_id", $movimientos_extras->pluck('id'))->get();
        //dd($movimiento_detalles_extras);
        $md_producto_ids = $movimiento_detalles->pluck('producto_id')->unique();
        $md_producto_ids_extras = $movimiento_detalles_extras->pluck('producto_id')->unique();

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
        $producto_ids = $producto_ids->merge($md_producto_ids_extras)->unique();

        // Obtener productos con datos mínimos
        $productos = DB::table('productos')->whereIn('id', $producto_ids)->get(['id', 'name', 'cantidad']);

        // Mapear datos para acceso rápido
        $movimientoMap = $movimiento_detalles->pluck('cantidad', 'producto_id');
        $cd_cantidad_comprobantesMap = $comprobante_detalles->pluck('cantidad_comprobantes', 'codProducto');
        $cd_total_cantidadMap = $comprobante_detalles->pluck('total_cantidad', 'codProducto');
        $movimientoMap_extras = $movimiento_detalles_extras->pluck('cantidad', 'producto_id');

        // Transformar productos agregando datos
        $this->productos = $productos->map(function ($producto) use ($movimientoMap, $cd_cantidad_comprobantesMap, $cd_total_cantidadMap, $movimientoMap_extras) {
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
        $this->view = 'agregar ingreso/salida';
        $this->regresa = true;
        $tipo_movimiento = TipoMovimiento::where("codigo", 101)->first();
        $this->tipo_movimiento_id = $tipo_movimiento->id;
        $this->tipo_movimiento_name = $tipo_movimiento->name;
    }

    public function agregar_salida()
    {
        $this->view = 'agregar ingreso/salida';
        $this->regresa = true;
        $tipo_movimiento = TipoMovimiento::where("codigo", 201)->first();
        $this->tipo_movimiento_id = $tipo_movimiento->id;
        $this->tipo_movimiento_name = $tipo_movimiento->name;
    }

    public function cargarProductos()
    {
        $sedes_id = auth_user()->user_empleado->empleado->fSede->empresa->sedes->pluck('id');
        $almacenes = Almacen::whereIn('f_sede_id', $sedes_id)->get();
        $lista_precio = 1;
        if (!$lista_precio) {
            return;
        }
        $this->listado_productos =  Producto::withTrashed()
            ->with([
                "marca:id,name", // optimizamos cargando solo 'nombre'
                "listaPrecios" => function ($query) use ($lista_precio) {
                    $query->where("lista_precio_id", $lista_precio)->select('producto_id', 'precio');
                },
                "almacenProductos" => function ($query) use ($almacenes) {
                    $query->whereIn("almacen_id", $almacenes->pluck("id"))->select('producto_id', 'stock_disponible');
                },
            ])
            ->get() //;dd($this->listado_productos->first());
            ->map(function ($producto) {
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->name,
                    'factor' => $producto->cantidad,
                    'marca' => $producto->marca->name ?? 'SIN MARCA',
                    'precio' => optional($producto->listaPrecios->first())->precio ?? 0,
                    'stock' => optional($producto->almacenProductos->first())->stock ?? 0,
                    'deleted_at' => $producto->deleted_at,
                ];
            })->values()->all();
    }

    public function updatedSearch()
    {
        $sedes_id = auth_user()->user_empleado->empleado->fSede->empresa->sedes->pluck('id');
        $this->almacenes = Almacen::whereIn('f_sede_id', $sedes_id)->get();
        $lista_precio = 1;
        if (!$lista_precio) {
            return;
        }

        if (strlen($this->search) > 0) {
            $this->search_productos = Producto::withTrashed()->where(function ($query) {
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
            $this->search_productos = [];
        }
    }

    public function agregarProducto($producto_id)
    {
        $lista_precio = 1;
        if (!$lista_precio) {
            // Mostrar mensaje de error o alerta
            return;
        }

        $producto = Producto::withTrashed()->with([
            "listaPrecios" => function ($query) use ($lista_precio) {
                $query->where("lista_precio_id", $lista_precio);
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
                "empleado_id" => auth_user()->user_empleado->empleado_id,
            ];

            usort($this->detalles, function ($a, $b) {
                return $b['producto_id'] <=> $a['producto_id'];
            });
            //}
        }

        // Limpiar búsqueda
        $this->search = "";
        $this->search_productos = [];
    }

    public function eliminarDetalle($index)
    {
        unset($this->detalles[$index]);
        $this->detalles = array_values($this->detalles);
    }

    public function guardarMovimiento()
    {
        $movimiento = Movimiento::find($this->movimiento_id);
        $datos_movimiento = [
            "almacen_id" => $movimiento->almacen_id,
            "tipo_movimiento_id" => $this->tipo_movimiento_id,
            "fecha_movimiento" => $movimiento->fecha_movimiento,
            "conductor_id" => $movimiento->conductor_id,
            "vehiculo_id" => $movimiento->vehiculo_id,
            "fecha_liquidacion" => $movimiento->fecha_liquidacion,
            "nro_doc_liquidacion" => $movimiento->nro_doc_liquidacion,
            "fecha_liquidacion" => $movimiento->fecha_liquidacion,
            "tipo_movimiento_name" => $this->tipo_movimiento_name,
            "empleado_id" => auth_user()->user_empleado->empleado_id,
            "estado" => "liquido",
        ];
        $array_validate = [
            "detalles" => "required",
        ];
        $this->validate($array_validate);

        try {
            Cache::lock('guardar_movimiento', 15)->block(10, function () use ($datos_movimiento) {
                DB::transaction(function () use ($datos_movimiento) {
                    $movimiento = Movimiento::create($datos_movimiento);
                    $movimiento->movimientoDetalles()->createMany($this->detalles);

                    $this->actualizarStock($movimiento);
                    $this->reset();
                    $this->mount();
                });
            });
        } catch (LockTimeoutException $e) {
            // Si no se pudo adquirir el bloqueo dentro del tiempo de espera, se lanzará una excepción.
            // Maneja la excepción según tu lógica de negocio.
            Log::error("No se pudo adquirir el bloqueo: " . $e->getMessage());
            throw new Exception("No se pudo adquirir el bloqueo: " . $e->getMessage());
        } catch (ParseError $e) {
            // Manejo de la excepción ParseError
            Log::error($e->getMessage() . "Monto maximo 999 999 999");
            throw new Exception($e->getMessage() . "Monto maximo 999 999 999");
        } catch (Exception $e) {
            // Manejar la excepción
            Log::error("No se pudo generar movimiento" . $e->getMessage());
            throw new Exception("No se pudo generar movimiento" . $e->getMessage());
        }
    }

    public function ajustarCantidad($index)
    {
        $detalle = $this->detalles[$index];
        $cantidad = number_format($detalle['cantidad'], 2, '.', '');

        // Separar la cantidad ingresada en cajas y paquetes
        if (strpos($cantidad, '.') !== false) {
            list($cajas, $paquetes) = explode('.', $cantidad);
            $cajas = (int)$cajas; // Convertir a entero
            $paquetes = (int)$paquetes; // Convertir a entero
        } else {
            $cajas = (int)$cantidad;
            $paquetes = 0;
        }

        // Validar que los paquetes no excedan la cantidad de productos por caja
        $producto = Producto::withTrashed()->find($detalle['producto_id']);
        $cantidadProducto = $producto->cantidad; // Cantidad de productos por caja

        if ($paquetes >= $cantidadProducto) {
            // Ajustar la cantidad si los paquetes son iguales o mayores que la cantidad de productos por caja
            $cajas += floor($paquetes / $cantidadProducto);
            $paquetes = $paquetes % $cantidadProducto; // Obtener el resto de paquetes
        }

        // Actualizar la cantidad en el detalle
        $this->detalles[$index]['cantidad'] = number_format($cajas + ($paquetes / 100), 2, '.', ''); // Convertir de nuevo a formato X.Y

        // Recalcular el importe
        $this->calcularImporte($index);
    }

    public function calcularImporte($index)
    {
        $detalle = $this->detalles[$index];
        $lista_precio = 1; // Asignar el ID de la lista de precios
        $producto = Producto::withTrashed()->find($detalle['producto_id']);

        if ($producto) {
            $precioCaja = $producto->listaPrecios->where('id', $lista_precio)->first()->pivot->precio ?? 0;
            $cantidadProducto = $producto->cantidad; // Cantidad de productos por caja

            // Validar que la cantidad del producto no sea cero
            if ($cantidadProducto <= 0) {
                //logger("Error: La cantidad del producto es cero o negativa para el producto:", [
                //    "producto_id" => $producto->id,
                //    "precioCaja" => $precioCaja,
                //    "cantidadProducto" => $cantidadProducto,
                //]);
                $this->detalles[$index]['precio_venta_total'] = 0;
                $this->detalles[$index]['costo_total'] = 0;
                return;
            }

            // Calcular precio por paquete
            $precioPorPaquete = $precioCaja / $cantidadProducto; // 108.00 / 36 = 3.00

            // Interpretar la cantidad ingresada
            $cantidad = $detalle['cantidad']; // Cantidad ingresada en cajas y paquetes

            // Separar la cantidad en cajas y paquetes
            $cajas = floor($cantidad); // Parte entera representa las cajas
            $paquetes = round(($cantidad - $cajas) * 100); // Parte decimal convertida a paquetes

            // Validar que los paquetes no excedan la cantidad de productos por caja
            if ($paquetes >= $cantidadProducto) {
                //logger("Error: La cantidad de paquetes no puede ser mayor o igual a la cantidad de productos por caja.", [
                //    "cantidadIngresada" => $cantidad,
                //    "paquetes" => $paquetes,
                //    "cantidadProducto" => $cantidadProducto,
                //]);
                $this->detalles[$index]['precio_venta_total'] = 0; // O puedes lanzar un mensaje de error
                $this->detalles[$index]['costo_total'] = 0;
                return;
            }

            // Calcular cantidad total de paquetes
            $cantidadPaquetes = ($cajas * $cantidadProducto) + $paquetes; // Total de paquetes

            // Calcular importe total
            $importe = $cantidadPaquetes * $precioPorPaquete; // Total de paquetes * precio por paquete

            // Actualizar el importe en el detalle
            $this->detalles[$index]['precio_venta_total'] = $importe;
            $this->detalles[$index]['costo_total'] = $importe;

            // Log para verificar el cálculo
            //logger("Cálculo de importe:", [
            //    "producto_id" => $producto->id,
            //    "precioCaja" => $precioCaja,
            //    "cantidadProducto" => $cantidadProducto,
            //    "cantidadIngresada" => $cantidad,
            //    "cajas" => $cajas,
            //    "paquetes" => $paquetes,
            //    "cantidadPaquetes" => $cantidadPaquetes,
            //    "precioPorPaquete" => $precioPorPaquete,
            //    "importeCalculado" => $importe,
            //]);
        }
    }
}
