<?php

namespace App\Livewire;

use App\Models\Almacen;
use App\Models\FComprobanteSunat;
use App\Models\FComprobanteSunatDetalle;
use App\Models\FGuiaSunat;
use App\Models\FSerie;
use App\Models\Movimiento;
use App\Models\MovimientoDetalle;
use App\Models\Producto;
use App\Models\TipoMovimiento;
use App\Traits\CalculosTrait;
use App\Traits\StockTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Luecano\NumeroALetras\NumeroALetras;
use ParseError;

class Liquidaciones extends Component
{
    public function render()
    {
        $this->dispatch('DataTable-initialize');
        return view('livewire.liquidaciones');
    }

    use StockTrait;
    use CalculosTrait;

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

        // Obtener detalles del movimiento principal
        $movimiento_detalles = MovimientoDetalle::where("movimiento_id", $movimiento_id)->get();
        $movimientos_extras_ingresos = Movimiento::with(['conductor', 'tipoMovimiento'])->where("nro_doc_liquidacion", $movimiento_id)->whereNotIn('id', [$movimiento_id])
            ->whereHas('tipoMovimiento', function ($q) {
                $q->where('tipo', 'ingreso');
            })->get();
        $movimientos_extras_salidas = Movimiento::with(['conductor', 'tipoMovimiento'])->where("nro_doc_liquidacion", $movimiento_id)->whereNotIn('id', [$movimiento_id])
            ->whereHas('tipoMovimiento', function ($q) {
                $q->where('tipo', 'salida');
            })->get();
        //dd($movimientos_extras_ingresos->toArray(), $movimientos_extras_ingresos->pluck('id'));
        $movimiento_detalles_extras_ingreso = MovimientoDetalle::whereIn("movimiento_id", $movimientos_extras_ingresos->pluck('id'))->get();
        //dd($movimiento_detalles_extras_ingreso->where('producto_id', 303)->sum('cantidad_total_unidades'));
        $md_producto_ids = $movimiento_detalles->pluck('producto_id')->unique();
        $md_producto_ids_extras_ingresos = $movimiento_detalles_extras_ingreso->pluck('producto_id')->unique();
        //dd($movimiento_detalles_extras_ingreso->toArray(), $md_producto_ids_extras_ingresos);

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
        //dd($movimiento_detalles_extras_ingreso, $movimiento_detalles_extras_ingreso->where('producto_id', 1197)->sum('cantidad_total_unidades'), $comprobante_detalles->where('codProducto', 1197)->sum('total_cantidad'), $movimiento_detalles->where('producto_id', 1197));
        $cd_producto_ids = $comprobante_detalles->pluck('codProducto')->unique();

        // Unir IDs de productos únicos
        $producto_ids = $cd_producto_ids->merge($md_producto_ids)->unique();
        $producto_ids = $producto_ids->merge($md_producto_ids_extras_ingresos)->unique();

        // Obtener productos con datos mínimos
        $productos = DB::table('productos')->whereIn('id', $producto_ids)->get(['id', 'name', 'cantidad']);

        // Mapear datos para acceso rápido
        $movimientoMap = $movimiento_detalles->pluck('cantidad', 'producto_id');
        $cd_cantidad_comprobantesMap = $comprobante_detalles->pluck('cantidad_comprobantes', 'codProducto');
        $cd_total_cantidadMap = $comprobante_detalles->pluck('total_cantidad', 'codProducto');
        //$movimientoMap_extras_ingresos = $movimiento_detalles_extras_ingreso->pluck('cantidad_total_unidades', 'producto_id');
        $movimientoMap_extras_ingresos = $movimiento_detalles_extras_ingreso->groupBy('producto_id')->map(fn($items) => $items->sum('cantidad_total_unidades'));

        // Transformar productos agregando datos
        $this->productos = $productos->map(function ($producto) use ($movimientoMap, $cd_cantidad_comprobantesMap, $cd_total_cantidadMap, $movimientoMap_extras_ingresos) {
            $movimientoCantidad = $movimientoMap[$producto->id] ?? 0;
            $totalComprobantesCantidad = $cd_total_cantidadMap[$producto->id] ?? 0;

            list($cajas, $paquetes) = explode(".", number_format($movimientoCantidad, calcular_digitos($producto->cantidad), '.', ''));

            $producto->movimiento_cantidad_cajas = $movimientoCantidad;
            $producto->movimiento_cantidad_en_paquetes = ($cajas * $producto->cantidad) + $paquetes;
            $producto->cantidad_comprobantes = $cd_cantidad_comprobantesMap[$producto->id] ?? 0;

            $producto->comprobantes_cantidad_cajas = number_format(
                intdiv($totalComprobantesCantidad, $producto->cantidad) + ($totalComprobantesCantidad % $producto->cantidad) / (10 ** calcular_digitos($producto->cantidad)),
                calcular_digitos($producto->cantidad),
                '.',
                ''
            );
            $producto->comprobantes_cantidad_paquetes = intval($totalComprobantesCantidad);

            $producto->diferencia_paquetes = $producto->movimiento_cantidad_en_paquetes - ($producto->comprobantes_cantidad_paquetes + ($movimientoMap_extras_ingresos[$producto->id] ?? 0));
            $producto->diferencia_cajas = number_format((intdiv($producto->diferencia_paquetes, $producto->cantidad) + ($producto->diferencia_paquetes % $producto->cantidad) / (10 ** calcular_digitos($producto->cantidad))), calcular_digitos($producto->cantidad), '.', '');
            $producto->extras_ingreso_paquetes = convertir_a_cajas(($movimientoMap_extras_ingresos[$producto->id] ?? 0), $producto->cantidad);

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
        $this->comprobantes = FComprobanteSunat::query()
            ->withSum('detalle as total_devuelto', 'valor_devuelto')
            ->select(
                'id',
                'movimiento_id',
                'tipoDoc_name',
                'serie',
                'correlativo',
                'cliente_id',
                'clientRazonSocial',
                'mtoImpVenta',
                'estado_reporte',
                'fechaEmision',
                DB::raw('COALESCE((select sum(valor_devuelto) from f_comprobante_sunat_detalles where f_comprobante_sunat_detalles.f_comprobante_sunat_id = f_comprobante_sunats.id), 0) as total_devuelto')
            )
            ->where('movimiento_id', $this->movimiento_id)
            ->whereNotIn('tipoDoc', ['07', '08'])
            ->get();

        //dd($this->comprobantes->first()->toArray());
    }

    // sin uso
    public function anular_cp($comprobante_id)
    {
        $this->por_anular[$comprobante_id] = false;

        // Buscar el comprobante y cambiar su estado
        if ($comprobante = $this->comprobantes->firstWhere('id', $comprobante_id)) {
            $comprobante->estado_reporte = false;
        }
    }

    // sin uso
    public function desanular_cp($comprobante_id)
    {
        $this->por_anular[$comprobante_id] = true;

        // Buscar el comprobante y restaurar su estado
        if ($comprobante = $this->comprobantes->firstWhere('id', $comprobante_id)) {
            $comprobante->estado_reporte = true;
        }
    }

    // sin uso
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

    // sin uso
    public function agregar_ingreso()
    {
        $this->view = 'agregar ingreso/salida';
        $this->regresa = true;
        $tipo_movimiento = TipoMovimiento::where("codigo", 101)->first();
        $this->tipo_movimiento_id = $tipo_movimiento->id;
        $this->tipo_movimiento_name = $tipo_movimiento->name;
    }

    // sin uso
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
                "cantidad_total_unidades" => convertir_a_paquetes(number_format($cantidad, 2, '.', ''), $producto->cantidad),
                "factor" => $producto->cantidad,
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
            Cache::lock('generar_movimiento', 15)->block(10, function () use ($datos_movimiento) {
                DB::transaction(function () use ($datos_movimiento) {
                    $movimiento = Movimiento::create($datos_movimiento);
                    $movimiento->movimientoDetalles()->createMany($this->detalles);

                    $this->actualizarStock($movimiento);
                    $this->reset('detalles');
                    //$this->mount();
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
        $this->detalles[$index]['cantidad'] = number_format($cajas + ($paquetes / (10 ** calcular_digitos($cantidadProducto))), calcular_digitos($cantidadProducto), '.', ''); // Convertir de nuevo a formato X.Y
        $this->detalles[$index]['cantidad_total_unidades'] = convertir_a_paquetes($this->detalles[$index]['cantidad'], $cantidadProducto);
        $this->detalles[$index]['factor'] = $cantidadProducto;

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
            $paquetes = round(($cantidad - $cajas) * (10 ** calcular_digitos($cantidadProducto))); // Parte decimal convertida a paquetes

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
            $importe = number_format_punto2($cantidadPaquetes * $precioPorPaquete); // Total de paquetes * precio por paquete

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

    public $modalDevolucion = false;
    public $comprobanteSeleccionado;
    public $comprobanteSeleccionado_array = [];
    public $detalleSeleccionado = [];

    public function mostrarDevolucion($id)
    {
        $this->comprobanteSeleccionado = FComprobanteSunat::find($id);
        $this->comprobanteSeleccionado_array = $this->comprobanteSeleccionado->toArray();

        $this->detalleSeleccionado = $this->comprobanteSeleccionado
            ? $this->comprobanteSeleccionado->detalle()->get()->toArray()
            : [];

        $this->modalDevolucion = true;
        $this->liquidacion_comprobantes();
    }

    public function cerrarModal()
    {
        $this->modalDevolucion = false;
        $this->liquidacion_comprobantes();
    }

    public function guardarDevoluciones($detalles)
    {
        $comprobante_id = 0;
        foreach ($detalles as $item) {
            FComprobanteSunatDetalle::where('id', $item['id'])->update([
                'cantidad_devuelta' => $item['cantidad_devuelta'] ?? 0,
                'valor_devuelto'    => ($item['cantidad_devuelta'] ?? 0) * ($item['mtoPrecioUnitario'] ?? 0),
            ]);
            $comprobante_id = FComprobanteSunatDetalle::where('id', $item['id'])->value('f_comprobante_sunat_id');
        }
        $comprobante = FComprobanteSunat::find($comprobante_id);
        $mov_detalles = FComprobanteSunatDetalle::where('f_comprobante_sunat_id', $comprobante_id)->where('cantidad_devuelta', '>', 0)->get();

        $tipo_movimiento = TipoMovimiento::where("codigo", 101)->first();
        $this->tipo_movimiento_id = $tipo_movimiento->id;
        $this->tipo_movimiento_name = $tipo_movimiento->name;

        foreach ($mov_detalles as $md) {
            $producto = Producto::withTrashed()->with([
                "listaPrecios" => function ($query) {
                    $query->where("lista_precio_id", 1);
                },
            ])->find($md->codProducto);
            $this->agregarProducto($md->codProducto);
            $indice = array_search($md->codProducto, array_column($this->detalles, 'producto_id'));
            $this->detalles[$indice]['cantidad'] = convertir_a_cajas($md->cantidad_devuelta, $producto->cantidad);
            $this->ajustarCantidad($indice);
        }
        $this->guardarMovimiento();
        $this->liquidacion_comprobantes();
        $this->anular($comprobante_id);
        $this->refacturar($comprobante_id);

        $comprobante->estado_reporte = false;
        $comprobante->save();

        $this->dispatch('notify', 'Devoluciones guardadas correctamente');
        $this->modalDevolucion = false; // cerrar modal
        $this->liquidacion_comprobantes();
    }

    public function anular($id)
    {
        $comprobante_guia = FComprobanteSunat::find($id);

        $comprobante_guia = $comprobante_guia->id ? $comprobante_guia : FGuiaSunat::find($id);
        $validando = match ($comprobante_guia->tipoDoc) {
            "00" => "nota_pedido",
            "01", "03" => false,
            default => true,
        };
        if ($validando == "nota_pedido") {
            $comprobante_guia->estado_reporte = false;
            $comprobante_guia->save();
            return;
        }
        if ($validando) {
            return;
        }

        $nota_anulacion_operacion = FComprobanteSunat::where('numDocfectado', $comprobante_guia->serie . "-" . $comprobante_guia->correlativo)
            ->where('tipoDoc', '07')
            ->where('codMotivo', '01')
            ->where('desMotivo', 'ANULACION DE LA OPERACION')
            ->exists();
        if ($nota_anulacion_operacion) {
            return;
        }

        $nota_anulacion = FComprobanteSunat::where('numDocfectado', $comprobante_guia->serie . "-" . $comprobante_guia->correlativo)
            ->where('tipoDoc', '07')
            ->exists();
        if ($nota_anulacion) {
            return;
        }

        try {
            Cache::lock('generar_nota', 15)->block(10, function () use ($id) {
                DB::beginTransaction();
                $tipoDoc = "07";
                $comprobante = FComprobanteSunat::with('detalle')->find($id);
                $serie = FSerie::where('f_sede_id', $comprobante->sede_id)->where('serie', 'like', substr($comprobante->serie, 0, 1) . "%")
                    ->whereHas('fTipoComprobante', function ($query) use ($tipoDoc) {
                        $query->where('tipo_comprobante', $tipoDoc);
                    })
                    ->get()->first();
                $serie->correlativo = $serie->correlativo + 1;
                $serie->save();

                $notaSunat = $comprobante->replicate();
                $notaSunat->fill([
                    //"conductor_id" => 10,
                    'movimiento_id' => null,
                    "ublVersion" => "2.1",
                    "tipoDoc" => $tipoDoc,
                    "tipoDoc_name" => $serie->fTipoComprobante->name,
                    "serie" => $serie->serie,
                    "correlativo" => $serie->correlativo,
                    "fechaEmision" => now(),
                    "tipDocAfectado" => $comprobante->tipoDoc,
                    "numDocfectado" => $comprobante->serie . "-" . $comprobante->correlativo,
                    "codMotivo" => "01",
                    "desMotivo" => "ANULACION DE LA OPERACION",
                    "nombrexml" => null,
                    "xmlbase64" => null,
                    "hash" => null,
                    "cdrxml" => null,
                    "cdrbase64" => null,
                    "codigo_sunat" => null,
                    "mensaje_sunat" => null,
                    "obs" => null,
                    "estado_reporte" => false,
                    "estado_cpe_sunat" => 'pendiente',
                ]);
                $notaSunat->save();
                $notaSunat->detalle()->createMany($comprobante->detalle->toArray());

                $comprobante->estado_reporte = false;
                $comprobante->save();
                //dd($serie, substr($comprobante->serie, 0, 1), $notaSunat, $comprobante->detalle->toArray());
                DB::commit();
            });
        } catch (Exception | LockTimeoutException $e) {
            DB::rollback();
            logger("Error al guardar comprobante nota:", ["error" => $e->getMessage()]);
            //throw $e; // Relanza la excepción si necesitas propagarla
            $this->dispatch("error-guardando-comprobante-nota", "Error al guardar comprobante nota" . "<br>" . $e->getMessage());
            $this->addError("error_guardar", $e->getMessage());
        }
    }

    public function refacturar($id)
    {
        $comprobante_guia = FComprobanteSunat::find($id);

        $comprobante_guia = $comprobante_guia->id ? $comprobante_guia : FGuiaSunat::find($id);
        $validando = match ($comprobante_guia->tipoDoc) {
            "00" => false,
            "01", "03" => false,
            default => true,
        };

        if ($validando) {
            return;
        }

        try {
            Cache::lock('generar_comprobante', 15)->block(10, function () use ($id) {
                DB::beginTransaction();
                $comprobante = FComprobanteSunat::with('detalle')->find($id);
                $tipoDoc = $comprobante->tipoDoc;
                $serie = FSerie::where('f_sede_id', $comprobante->sede_id)->where('serie', 'like', substr($comprobante->serie, 0, 1) . "%")
                    ->whereNotIn('serie', [$comprobante->serie])
                    ->whereHas('fTipoComprobante', function ($query) use ($tipoDoc) {
                        $query->where('tipo_comprobante', $tipoDoc);
                    })
                    ->get()->first();
                $serie->correlativo = $serie->correlativo + 1;
                $serie->save();

                $lote_detalles = [];
                foreach ($comprobante->detalle as $det) {
                    $lote_det = [];
                    if (($det->cantidad - $det->cantidad_devuelta) > 0) {
                        $lote_det['producto_id'] = $det->codProducto;
                        $lote_det['producto_name'] = $det->descripcion;
                        $lote_det['cantidad'] = convertir_a_cajas(($det->cantidad - $det->cantidad_devuelta), $det->ref_producto_cantidad_cajon);
                        $lote_det['producto_precio'] = $det->ref_producto_precio_cajon;
                        $lote_det['producto_cantidad_caja'] = $det->ref_producto_cantidad_cajon;
                        $lote_det['lista_precio'] = $det->ref_producto_lista_precio;
                        $lote_det['importe'] = $det->mtoValorVenta + $det->totalImpuestos - $det->valor_devuelto;
                        $lote_det['peso'] = $det->peso; //calcular
                        $lote_det['cantidad_unidades'] = ($det->cantidad - $det->cantidad_devuelta);
                        $lote_detalles[] = $lote_det;
                    }
                }
                logger("Detalles para refacturar:", ["detalles" => $lote_detalles]);

                if (count($lote_detalles) === 0) {
                    throw new \Exception("No hay detalles válidos para refacturar.");
                }

                $formatter = new NumeroALetras();
                list($subtotales, $detalles) = ($this->setSubTotalesIgv($lote_detalles, true));
                $subtotales = (object)$subtotales;
                $datos_comprobante = [];
                $datos_comprobante = [
                    //"conductor_id" => 10,
                    //'pedido_fecha_factuacion' => $this->fecha_proceso,
                    'movimiento_id' => null,
                    'ublVersion' => '2.1',
                    'tipoOperacion' => '0101',
                    "tipoDoc_name" => $serie->fTipoComprobante->name,
                    'serie' => $serie->serie,
                    'correlativo' => $serie->correlativo,
                    //'fechaEmision' => $this->fecha_reparto,
                    "fechaEmision" => now(),
                    'mtoOperGravadas' => $subtotales->mtoOperGravadas,
                    'mtoOperInafectas' => $subtotales->mtoOperInafectas,
                    'mtoOperExoneradas' => $subtotales->mtoOperExoneradas,
                    'mtoOperGratuitas' => $subtotales->mtoOperGratuitas,
                    'mtoIGV' => $subtotales->mtoIGV,
                    'mtoBaseIsc' => 0,
                    'mtoISC' => 0,
                    'icbper' => 0,
                    'totalImpuestos' => $subtotales->totalImpuestos,
                    'valorVenta' => $subtotales->valorVenta,
                    'subTotal' => $subtotales->subTotal,
                    'redondeo' => $subtotales->redondeo,
                    'mtoImpVenta' => $subtotales->mtoImpVenta,
                    'legendsCode' => 1000,
                    'legendsValue' => $formatter->toInvoice($subtotales->mtoImpVenta, 2, 'SOLES'),
                    "tipDocAfectado" => $comprobante->tipoDoc,
                    "numDocfectado" => $comprobante->serie . "-" . $comprobante->correlativo,
                    'codMotivo' => null,
                    'desMotivo' => null,
                    'nombrexml' => null,
                    'xmlbase64' => null,
                    'hash' => null,
                    'cdrbase64' => null,
                    'cdrxml' => null,
                    'codigo_sunat' => null,
                    'mensaje_sunat' => null,
                    'obs' => null,
                    //'fecha' => now(),
                    "estado_reporte" => true,
                    "estado_cpe_sunat" => 'pendiente',
                    // Otros campos necesarios
                ];

                $comprobante_new = $comprobante->replicate();
                $comprobante_new->fill($datos_comprobante);
                $comprobante_new->save();
                $comprobante_new->detalle()->createMany($detalles);
                if ($comprobante_new->tipoDoc == '00') {
                    $comprobante_new->estado_cpe_sunat = 'aceptado';
                    $comprobante_new->save();
                }
                //dd($serie, substr($comprobante->serie, 0, 1), $comprobante_new, $comprobante->detalle->toArray());
                DB::commit();
            });
        } catch (Exception | LockTimeoutException $e) {
            DB::rollback();
            logger("Error al guardar comprobante refacturado:", ["error" => $e->getMessage()]);
            //throw $e; // Relanza la excepción si necesitas propagarla
            $this->dispatch("error-guardando-comprobante-refacturado", "Error al guardar comprobante refacturado" . "<br>" . $e->getMessage());
            $this->addError("error_guardar", $e->getMessage());
        }
    }
}
