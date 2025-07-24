<?php

namespace App\Livewire;

use App\Models\Empleado;
use App\Models\Pedido;
use Carbon\Carbon;
use Livewire\Component;
use App\Models\PedidoDetalle;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use App\Traits\CalculosTrait;
use App\Traits\StockTrait;
use Exception;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;

class PedidoReporteDiario extends Component
{
    use CalculosTrait;
    use StockTrait;
    public $fecha;
    public $totales = [
        "valorVenta" => 0,
        "totalImpuestos" => 0,
        "subTotal" => 0,
    ];

    public function mount()
    {
        $this->fecha = now()->format("Y-m-d");
    }

    public function updatedFecha()
    {
        $this->dispatch("fecha-updated");
    }

    public function render()
    {
        $user = auth_user();
        $fechaBusqueda = Carbon::parse($this->fecha);

        if ($user->hasRole("admin")) {
            $pedidosPorVendedor = Pedido::where("fecha_emision", $fechaBusqueda)
                ->with([
                    "vendedor",
                    "ruta",
                    "cliente",
                    "listaPrecio",
                    "pedidoDetalles.producto" => function ($query) {
                        $query->withTrashed()->with(["marca"]);
                    },
                ])
                ->get()
                ->groupBy("vendedor_id");
        } else {
            $vendedorId = $user->empleados->first()->id;
            $pedidosPorVendedor = Pedido::where("fecha_emision", $fechaBusqueda)
                ->where("vendedor_id", $vendedorId)
                ->with([
                    "vendedor",
                    "ruta",
                    "cliente",
                    "listaPrecio",
                    "pedidoDetalles.producto" => function ($query) {
                        $query->withTrashed()->with(["marca"]);
                    },
                ])
                ->orderBy("vendedor_id")
                ->get()
                ->groupBy("vendedor_id");
        }

        $detalles = $pedidosPorVendedor->flatten()->flatMap(function ($pedido) {
            return $pedido->pedidoDetalles->map(function ($detalle) use ($pedido) {
                // Agregar campo personalizado
                $detalle->total_cantidad_unidades = convertir_a_paquetes($detalle->cantidad, $detalle->producto_cantidad_caja);
                $detalle->cliente_id = $pedido->cliente_id;
                $detalle->producto_marca_id = $detalle->producto->marca_id;
                $detalle->producto_marca_name = $detalle->producto->marca->name;
                return $detalle;
            });
        });

        // Agrupamos por producto_id y resumimos la info
        $resumenPorProducto = $detalles->groupBy('producto_id')->map(function ($detallesProducto, $productoId) use ($detalles) {
            // $detallesProducto - es una collection de PedidoDetalle; detalles agrupados por producto_id
            $primerDetalle = $detallesProducto->first(); // Para tomar datos del producto
            $totalUnidades = $detallesProducto->sum('total_cantidad_unidades');
            return [
                'producto_id'             => $productoId,
                'producto_name'           => $primerDetalle->producto_name,
                'producto_cantidad_caja'  => $primerDetalle->producto_cantidad_caja,
                'producto_marca'          => $primerDetalle->producto_marca_id,
                'total_cantidad_unidades' => $totalUnidades,
                'cantidad_bultos'         => intdiv($totalUnidades, $primerDetalle->producto_cantidad_caja),
                'cantidad_unidades'       => $totalUnidades % $primerDetalle->producto_cantidad_caja,
                'suma_importe'            => $detallesProducto->sum('importe'),
            ];
        })->sortBy('producto_id')->values(); // ->values() si deseas que el índice sea 0,1,2... en lugar del producto_id

        $reportePorMarca = $detalles->groupBy('producto_marca_id')->map(function ($grupo) {
            return [
                'marca_id' => $grupo->first()->producto_marca_id,
                'marca_name' => $grupo->first()->producto_marca_name,
                'clientes_unicos' => $grupo->pluck('cliente_id')->unique()->count(),
                'importe_total' => $grupo->sum('importe'),
            ];
        })->sortKeys();
        //dd($resumenPorProducto, $detalles->first());

        return view("livewire.pedido-reporte-diario", [
            "pedidosPorVendedor" => $pedidosPorVendedor,
            "resumenPorProducto" => $resumenPorProducto,
        ]);
    }

    public $search = "";
    public $productos = [];
    public $pedidoEnEdicion = null;
    public $detallesEdit = [];
    public $comentarios;
    public $vendedor_id = null;

    public function editarPedido($pedidoId)
    {
        $this->pedidoEnEdicion = Pedido::with([
            "cliente",
            "pedidoDetalles.producto", // Incluir la relación con producto
        ])->find($pedidoId);

        $this->comentarios = $this->pedidoEnEdicion->comentario;
        $this->vendedor_id = $this->pedidoEnEdicion->vendedor_id;

        foreach ($this->pedidoEnEdicion->pedidoDetalles as $detalle) {
            $this->detallesEdit[$detalle->id] = [
                "cantidad" => $detalle->cantidad,
                "importe" => $detalle->importe,
            ];
        }

        // Calcular totales iniciales
        $detallesArray = $this->pedidoEnEdicion->pedidoDetalles
            ->map(function ($detalle) {
                return [
                    "producto_id" => $detalle->producto_id,
                    "nombre" => $detalle->producto_name,
                    "cantidad" => $detalle->cantidad,
                    "importe" => $detalle->importe,
                    "tipAfeIgv" =>
                    $detalle->producto->f_tipo_afectacion_id ?? 10,
                    "tipSisIsc" => "01",
                    "producto_precio" => $detalle->producto_precio,
                    "producto_cantidad_caja" => $detalle->producto_cantidad_caja,
                    "lista_precio" => $detalle->lista_precio,
                ];
            })
            ->toArray();

        $this->totales = $this->setSubTotalesIgv($detallesArray);

        $this->dispatch("open-modal");
    }

    public function updatedSearch()
    {
        if (strlen($this->search) > 0) {
            $this->productos = Producto::where(function ($query) {
                $query
                    ->where("name", "like", "%" . $this->search . "%")
                    ->orWhere("id", "like", "%" . $this->search . "%");
            })
                ->with([
                    "marca",
                    "listaPrecios" => function ($query) {
                        $query->where(
                            "lista_precio_id",
                            $this->pedidoEnEdicion->lista_precio
                        );
                    },
                ])
                ->take(15)
                ->get();

            // Debug para verificar los precios
            // logger("Productos encontrados:", [
            //     "lista_precio" => $this->pedidoEnEdicion->lista_precio,
            //     "productos" => $this->productos->map(function ($producto) {
            //         return [
            //             "id" => $producto->id,
            //             "name" => $producto->name,
            //             "precio" => $producto->listaPrecios->first()?->pivot
            //                 ?->precio,
            //         ];
            //     }),
            // ]);
        } else {
            $this->productos = [];
        }
    }

    public function agregarProducto($productoId)
    {
        // $this->pedidoEnEdicion,  colecction | Pedido
        // $detalleExistente,       colecction | PedidoDetalle
        // $this->detallesEdit),    array

        $this->resetValidation();
        try {
            DB::beginTransaction();
            Cache::lock('guardar_pedido', 15)->block(10, function () use ($productoId) {
            $almacen_id = Empleado::with(['fSede.almacen'])->find($this->pedidoEnEdicion->vendedor_id)->fSede->almacen->id;
            $producto = Producto::withTrashed()->with([
                "listaPrecios" => function ($query) {
                    $query->where(
                        "lista_precio_id",
                        $this->pedidoEnEdicion->lista_precio
                    );
                },
                'almacenProductos' => fn($q) => $q->where("almacen_id", $almacen_id),
            ])->find($productoId);

            if (!$producto) {
                throw new \Exception("Producto no encontrado");
            }
            if (!$producto->almacenProductos->first()) {
                throw new \Exception("Este producto aún no ha sido ingresado en almacén. Sin Stock Disponible.");
            }

            $precio = $producto->listaPrecios->first()?->pivot?->precio ?? 0;

            // Verificar si el producto ya existe en el pedido
            $detalleExistente = $this->pedidoEnEdicion
                ->pedidoDetalles()
                ->where("producto_id", $productoId)
                ->first();

            if ($detalleExistente) {
                // Si existe, aumentar cantidad
                $nuevaCantidad =
                    $producto->cantidad == 1
                    ? $detalleExistente->cantidad + 1
                    : $detalleExistente->cantidad + 0.01;

                $nuevaCantidad = $this->ajustarCantidadDetalle(
                    $nuevaCantidad,
                    $producto->cantidad
                );

                $nuevoImporte = $this->calcularImporteDetalle(
                    $nuevaCantidad,
                    $precio,
                    $producto->cantidad,
                    $producto->f_tipo_afectacion_id
                );

                $detail = $detalleExistente;
                $cant_actual = convertir_a_paquetes(
                    $detail->cantidad,
                    $detail->producto_cantidad_caja
                );
                $cant_nueva = convertir_a_paquetes(
                    $nuevaCantidad,
                    $detail->producto_cantidad_caja
                );
                $cant_diferencia = convertir_a_cajas(($cant_nueva - $cant_actual),
                $detail->producto_cantidad_caja);
                $detail->cantidad = ($cant_diferencia < 0) ? 0 : $cant_diferencia;
                $detail->importe = $nuevoImporte;
                $array_detalles[] = $detail->toArray();

                $this->validar_stock_precio($array_detalles);

                $this->actualizar_stock(array($detalleExistente), true);
                // Actualizar el detalleExistente existente directamente en bd (mejorar)
                $detalleExistente->update([
                    "cantidad" => $nuevaCantidad,
                    "importe" => $nuevoImporte,
                ]);
                $this->actualizar_stock(array($detalleExistente), false);

                // Actualizar detallesEdit para el producto existente
                $this->detallesEdit[$detalleExistente->id] = [
                    "cantidad" => $nuevaCantidad,
                    "importe" => $nuevoImporte,
                ];
                //dd($this->pedidoEnEdicion, $detalleExistente, $nuevaCantidad, $nuevoImporte, $this->detallesEdit);
            } else {
                // Si no existe, crear nuevo detalle
                $cantidad = $producto->cantidad == 1 ? 1 : 0.01;
                $importe =
                    $producto->cantidad == 1
                    ? $precio
                    : $precio / $producto->cantidad;

                if ($producto->f_tipo_afectacion_id == '21') {
                    $importe = 0;
                }
                $importe = number_format_punto2($importe);
                // Crear el nuevo detalle directamente en bd (mejorar)
                $nuevoDetalle = $this->pedidoEnEdicion
                ->pedidoDetalles()
                ->create([
                    "producto_id" => $productoId,
                    "producto_name" => $producto->name,
                    "producto_precio" => $precio,
                    "cantidad" => $cantidad,
                    "importe" => $importe,
                    "producto_cantidad_caja" => $producto->cantidad,
                    "lista_precio" => $this->pedidoEnEdicion->lista_precio,
                    "almacen_producto_id" => $producto->almacenProductos->first()->id,
                ]);
                $this->validar_stock_precio(array($nuevoDetalle));
                $this->actualizar_stock(array($nuevoDetalle), false);

                // Agregar el nuevo detalle a detallesEdit
                $this->detallesEdit[$nuevoDetalle->id] = [
                    "cantidad" => $cantidad,
                    "importe" => $importe,
                ];
            }

            // Recalcular totales
            $detallesArray = $this->pedidoEnEdicion
                ->pedidoDetalles()
                ->get()
                ->map(function ($detalle) {
                    return [
                        "producto_id" => $detalle->producto_id,
                        "nombre" => $detalle->producto_name,
                        "cantidad" => $detalle->cantidad,
                        "importe" => $detalle->importe,
                        "tipAfeIgv" => $detalle->producto->f_tipo_afectacion_id ?? 10,
                        "tipSisIsc" => "01",
                        "producto_precio" => $detalle->producto_precio,
                        "producto_cantidad_caja" => $detalle->producto_cantidad_caja,
                        "lista_precio" => $detalle->lista_precio,
                    ];
                })
                ->toArray();

            $this->totales = $this->setSubTotalesIgv($detallesArray);

            // Actualizar el pedido
            $this->pedidoEnEdicion->update([
                "valor_venta" => $this->totales["valorVenta"],
                "total_impuestos" => $this->totales["totalImpuestos"],
                "importe_total" => $this->totales["subTotal"],
            ]);

            // Recargar el pedido
            $this->pedidoEnEdicion = $this->pedidoEnEdicion->fresh([
                "pedidoDetalles.producto",
            ]);

            // Limpiar búsqueda
            $this->search = "";
            $this->productos = [];

            DB::commit();

            $this->dispatch("notify", [
                "message" => "Producto agregado correctamente",
                "type" => "success",
            ]);
        });
        } catch (Exception | LockTimeoutException $e) {
            DB::rollBack();
            $this->addError("error_guardar", $e->getMessage());
            logger("Error al agregar producto:", [
                "error" => $e->getMessage(),
                //"stack" => $e->getTraceAsString(),
            ]);
            $this->dispatch("notify", [
                "message" => "Error al agregar producto: " . $e->getMessage(),
                "type" => "error",
            ]);
        }
    }

    public function actualizarCantidad($detalleId, $cantidad)
    {
        try {
            DB::beginTransaction();

            $detalle = PedidoDetalle::find($detalleId);
            if (!$detalle) {
                throw new \Exception("Detalle no encontrado");
            }

            // Calcular nuevo importe
            $nuevoImporte = $detalle->producto_precio * $cantidad;

            // Actualizar detalle
            $detalle->update([
                "cantidad" => $cantidad,
                "importe" => $nuevoImporte,
            ]);

            // Actualizar importe total del pedido
            $this->actualizarTotalesPedido($detalle->pedido_id);

            $this->detallesEdit[$detalleId]["importe"] = $nuevoImporte;

            DB::commit();
            $this->dispatch("notify", [
                "message" => "Cantidad actualizada correctamente",
                "type" => "success",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch("notify", [
                "message" => "Error al actualizar: " . $e->getMessage(),
                "type" => "error",
            ]);
        }
    }

    public function eliminarDetalle($detalleId)
    {
        try {
            DB::beginTransaction();

            $detalle = PedidoDetalle::find($detalleId);
            if (!$detalle) {
                throw new \Exception("Detalle no encontrado");
            }

            // Guardar el ID del pedido antes de eliminar el detalle
            $pedidoId = $detalle->pedido_id;

            $this->actualizar_stock(array($detalle), true);
            // Eliminar el detalle bd (mejorar)
            $detalle->delete();

            // Eliminar de detallesEdit si existe
            if (isset($this->detallesEdit[$detalleId])) {
                unset($this->detallesEdit[$detalleId]);
            }

            // Recalcular totales con los detalles restantes
            $detallesArray = $this->pedidoEnEdicion
                ->pedidoDetalles()
                ->get()
                ->map(function ($detalle) {
                    return [
                        "producto_id" => $detalle->producto_id,
                        "nombre" => $detalle->producto_name,
                        "cantidad" => $detalle->cantidad,
                        "importe" => $detalle->importe,
                        "tipAfeIgv" =>
                        $detalle->producto->f_tipo_afectacion_id ?? 10,
                        "tipSisIsc" => "01",
                        "producto_precio" => $detalle->producto_precio,
                        "producto_cantidad_caja" => $detalle->producto_cantidad_caja,
                        "lista_precio" => $detalle->lista_precio,
                    ];
                })
                ->toArray();

            $this->totales = $this->setSubTotalesIgv($detallesArray);

            // Actualizar el pedido con los nuevos totales
            $this->pedidoEnEdicion->update([
                "valor_venta" => $this->totales["valorVenta"],
                "total_impuestos" => $this->totales["totalImpuestos"],
                "importe_total" => $this->totales["subTotal"],
            ]);

            // Recargar el pedido para actualizar la vista
            $this->pedidoEnEdicion = $this->pedidoEnEdicion->fresh([
                "pedidoDetalles.producto",
            ]);

            DB::commit();

            $this->dispatch("notify", [
                "message" => "Producto eliminado correctamente",
                "type" => "success",
            ]);
        } catch (Exception | LockTimeoutException $e) {
            DB::rollBack();
            logger("Error al eliminar detalle:", [
                "error" => $e->getMessage(),
                "stack" => $e->getTraceAsString(),
            ]);
            $this->dispatch("notify", [
                "message" =>
                "Error al eliminar el producto: " . $e->getMessage(),
                "type" => "error",
            ]);
            $this->dispatch("error-guardando-pedido", "Error " . "<br>" . $e->getMessage());
            $this->addError("error_guardar", $e->getMessage());
        }
    }

    private function actualizarTotalesPedido($pedidoId)
    {
        // Obtener los detalles del pedido actualizados
        $detalles = PedidoDetalle::where("pedido_id", $pedidoId)
            ->get()
            ->map(function ($detalle) {
                return [
                    "importe" => $detalle->importe,
                ];
            })
            ->toArray();
        //dd($detalles);
        // Usar el mismo método del trait para calcular los totales
        $totalesTemp = $this->setSubTotalesIgv($detalles);
        $this->totales = [
            "valorVenta" => $totalesTemp["valorVenta"],
            "totalImpuestos" => $totalesTemp["totalImpuestos"],
            "subTotal" => $totalesTemp["subTotal"],
        ];

        // Actualizar el pedido con los nuevos totales
        Pedido::where("id", $pedidoId)->update([
            "valor_venta" => $this->totales["valorVenta"],
            "total_impuestos" => $this->totales["totalImpuestos"],
            "importe_total" => $this->totales["subTotal"],
        ]);
    }

    public $cambiosTemporales = [];
    public function guardarCambios()
    {
        $this->resetValidation();

        try {
            DB::beginTransaction();
            Cache::lock('guardar_pedido', 15)->block(10, function () {
            $array_detalles = [];
            //dd($this->cambiosTemporales);
            foreach ($this->cambiosTemporales as $detalleId => $cambio) {
                $detail = PedidoDetalle::find($detalleId);
                $cant_actual = convertir_a_paquetes(
                    $detail->cantidad,
                    $detail->producto_cantidad_caja
                );
                $cant_nueva = convertir_a_paquetes(
                    $cambio["cantidad"],
                    $detail->producto_cantidad_caja
                );
                $cant_diferencia = convertir_a_cajas(($cant_nueva - $cant_actual),
                    $detail->producto_cantidad_caja);
                $detail->cantidad = ($cant_diferencia < 0) ? 0 : $cant_diferencia;
                $detail->importe = $cambio["importe"];
                $array_detalles[] = $detail->toArray();
            }
            $this->validar_stock_precio($array_detalles);

            // Aplicar los cambios temporales
            foreach ($this->cambiosTemporales as $detalleId => $cambio) {
                $detalle = PedidoDetalle::find($detalleId);
                $this->actualizar_stock(array($detalle), true);
                $detalle->update([
                    "cantidad" => $cambio["cantidad"],
                    "importe" => $cambio["importe"],
                ]);
                $this->actualizar_stock(array($detalle), false);
            }

            // Recalcular totales basados en los detalles actualizados
            $detalles = $this->pedidoEnEdicion->pedidoDetalles()->get();
            $detallesArray = $detalles
                ->map(function ($detalle) {
                    return [
                        "producto_id" => $detalle->producto_id,
                        "nombre" => $detalle->producto_name,
                        "cantidad" => $detalle->cantidad,
                        "importe" => $detalle->importe,
                        "tipAfeIgv" =>
                        $detalle->producto->f_tipo_afectacion_id ?? 10,
                        "tipSisIsc" => "01",
                        "producto_precio" => $detalle->producto_precio,
                        "producto_cantidad_caja" => $detalle->producto_cantidad_caja,
                        "lista_precio" => $detalle->lista_precio,
                    ];
                })
                ->toArray();

            $totalesTemp = $this->setSubTotalesIgv($detallesArray);

            // Actualizar el pedido con los valores calculados
            $this->pedidoEnEdicion->update([
                "comentario" => $this->comentarios,
                "valor_venta" => $totalesTemp["valorVenta"],
                "total_impuestos" => $totalesTemp["totalImpuestos"],
                "importe_total" => $totalesTemp["subTotal"],
                "monto_redondeo" => $totalesTemp["redondeo"] ?? 0,
            ]);

            DB::commit();

            // Limpiar cambios temporales
            $this->cambiosTemporales = [];

            $this->dispatch("notify", [
                "message" => "Cambios guardados correctamente",
                "type" => "success",
            ]);

            $this->dispatch("close-modal");
            $this->pedidoEnEdicion = null;
            $this->detallesEdit = [];
            $this->comentarios = "";

            $this->mount();
            });
        } catch (Exception | LockTimeoutException $e) {
            DB::rollBack();
            $this->addError("error_guardar", $e->getMessage());
            $this->dispatch("notify", [
                "message" =>
                "Error al guardar los cambios: " . $e->getMessage(),
                "type" => "error",
            ]);
        }
    }

    public function actualizarCantidadDetalle($detalleId, $nuevaCantidad)
    {
        try {
            // Obtiene el detalle del pedido y el producto
            $detalle = PedidoDetalle::find($detalleId);
            $producto = Producto::withTrashed()->find($detalle->producto_id);

            // Procesa la cantidad según el tipo de producto
            if ($producto->cantidad == 1) {
                $cantidad = $this->ajustarCantidadDetalle(
                    $nuevaCantidad,
                    $producto->cantidad
                );
                $importe = $cantidad * $detalle->producto_precio;
                if ($producto->f_tipo_afectacion_id == 21) {
                    $importe = 0;
                }
            } else {
                $cantidad = $this->ajustarCantidadDetalle(
                    $nuevaCantidad,
                    $producto->cantidad
                );
                $importe = $this->calcularImporteDetalle(
                    $cantidad,
                    $detalle->producto_precio,
                    $producto->cantidad,
                    $producto->f_tipo_afectacion_id
                );
            }

            // Almacena los cambios temporalmente
            $this->cambiosTemporales[$detalleId] = [
                "cantidad" => $cantidad,
                "importe" => $importe,
            ];

            // Actualiza el array de detalles en edición (solo para visualización)
            $this->detallesEdit[$detalleId] = [
                "cantidad" => $cantidad,
                "importe" => $importe,
            ];

            // Recalcula totales temporales
            $detallesArray = $this->pedidoEnEdicion->pedidoDetalles
                ->map(function ($det) use ($detalleId) {
                    $cambio = $this->cambiosTemporales[$det->id] ?? null;
                    return [
                        "producto_id" => $det->producto_id,
                        "cantidad" => $cambio
                            ? $cambio["cantidad"]
                            : $det->cantidad,
                        "importe" => $cambio
                            ? $cambio["importe"]
                            : $det->importe,
                        "tipAfeIgv" =>
                        $det->producto->f_tipo_afectacion_id ?? 10,
                        "tipSisIsc" => "01",
                        "producto_precio" => $det->producto_precio,
                        "producto_cantidad_caja" => $det->producto_cantidad_caja,
                        "lista_precio" => $det->lista_precio,
                    ];
                })
                ->toArray();

            $this->totales = $this->setSubTotalesIgv($detallesArray);

            $this->dispatch("notify", [
                "message" =>
                "Cantidad actualizada. No olvide guardar los cambios.",
                "type" => "info",
            ]);
        } catch (\Exception $e) {
            $this->dispatch("notify", [
                "message" =>
                "Error al actualizar cantidad: " . $e->getMessage(),
                "type" => "error",
            ]);
        }
    }

    private function ajustarCantidadDetalle($cantidad, $cantidadPorCaja)
    {
        // Convierte el valor en número
        $cantidad = ($cantidad == "" ? 0 : $cantidad);
        $cantidad = number_format_punto2($cantidad <= 0 ? 0.01 : $cantidad);

        // Si la cantidad es menor a 1, asumimos que son paquetes
        if ($cantidad < 1) {
            $paquetes = round($cantidad * 100); // Convertir a paquetes
            if ($paquetes >= $cantidadPorCaja) {
                $cajas = floor($paquetes / $cantidadPorCaja);
                $paquetes = $paquetes % $cantidadPorCaja;
                return $cajas . "." . str_pad($paquetes, 2, "0", STR_PAD_LEFT);
            }
            return "0." . str_pad($paquetes, 2, "0", STR_PAD_LEFT);
        }

        // Para cantidades mayores a 1 (cajas)
        $cajas = floor($cantidad);
        $paquetes = round(($cantidad - $cajas) * 100);

        if ($paquetes >= $cantidadPorCaja) {
            $cajas += floor($paquetes / $cantidadPorCaja);
            $paquetes = $paquetes % $cantidadPorCaja;
        }

        return $cajas . "." . str_pad($paquetes, 2, "0", STR_PAD_LEFT);
    }

    private function calcularImporteDetalle(
        $cantidad,
        $precioCaja,
        $cantidadPorCaja,
        $f_tipo_afectacion_id = null
    ) {
        $cantidad = number_format_punto2($cantidad);
        // Separar cajas y paquetes
        list($cajas, $paquetes) = explode(".", $cantidad);
        $cajas = intval($cajas);
        $paquetes = intval($paquetes);

        if ($f_tipo_afectacion_id == 21) {
            $precioCaja = 0;
        }

        // Calcular precio por paquete
        $precioPorPaquete = $precioCaja / $cantidadPorCaja;

        // Calcular importe total
        $importeCajas = $cajas * $precioCaja;
        $importePaquetes = $paquetes * $precioPorPaquete;

        return number_format($importeCajas + $importePaquetes, 2, '.', '');
    }

    public function eliminarPedido()
    {
        try {
            if (!$this->pedidoEnEdicion) {
                throw new \Exception(
                    "No hay pedido seleccionado para eliminar"
                );
            }

            DB::beginTransaction();

            $this->actualizarStock($this->pedidoEnEdicion, true);

            // Eliminamos los detalles del pedido
            $this->pedidoEnEdicion->pedidoDetalles()->each(function ($detalle) {
                $detalle->delete(); // Esto sí audita
            });

            // Luego eliminamos el pedido
            $this->pedidoEnEdicion->delete();

            DB::commit();

            $this->dispatch("notify", [
                "message" => "Pedido eliminado correctamente",
                "type" => "success",
            ]);

            // Cerrar el modal y limpiar el estado
            $this->dispatch("close-modal");
            $this->pedidoEnEdicion = null;
            $this->detallesEdit = [];
            $this->comentarios = "";

            // Recargar los datos
            $this->mount();
        } catch (Exception | LockTimeoutException $e) {
            DB::rollBack();
            logger("Error pedido (eliminado):", ["error" => $e->getMessage()]);
            $this->dispatch("notify", [
                "message" => "Error al eliminar el pedido: " . $e->getMessage(),
                "type" => "error",
            ]);
        }
    }

    public function validar_stock_precio($array_detalles)
    {
        try {
            // Validar stock y precio de los detalles
            $almacen_id = Empleado::with(['fSede.almacen'])->find($this->vendedor_id)->fSede->almacen->id;
            $this->validarStock_arraydetalles($array_detalles, $almacen_id);
            $this->validarPrecio_arraydetalles($array_detalles, $almacen_id);
        } catch (Exception | LockTimeoutException $e) {
            DB::rollback();
            logger("Error pedido (edicion):", ["error" => $e->getMessage()]);
            throw $e; // Relanza la excepción si necesitas propagarla
        }
    }

    public function actualizar_stock($pedido_detalles, $anulando = false)
    {
        Cache::lock('actualizar_stock', 15)->block(10, function () use ($pedido_detalles, $anulando) {
            $almacen_id = Empleado::with(['fSede.almacen'])->find($this->vendedor_id)->fSede->almacen->id;
            foreach ($pedido_detalles as $detalle) {
                $this->actualizarStockDetalle($detalle, $almacen_id, $anulando);
            }
        });
    }
}
