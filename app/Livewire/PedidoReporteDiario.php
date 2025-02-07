<?php

namespace App\Livewire;

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
        $user = auth()->user();
        $fechaBusqueda = Carbon::parse($this->fecha);

        if ($user->hasRole("admin")) {
            $pedidosPorVendedor = Pedido::where("fecha_emision", $fechaBusqueda)
                ->with([
                    "vendedor",
                    "ruta",
                    "cliente",
                    "listaPrecio",
                    "pedidoDetalles.producto",
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
                    "pedidoDetalles.producto",
                ])
                ->get()
                ->groupBy("vendedor_id");
        }

        return view("livewire.pedido-reporte-diario", [
            "pedidosPorVendedor" => $pedidosPorVendedor,
        ]);
    }

    public $search = "";
    public $productos = [];
    public $pedidoEnEdicion = null;
    public $detallesEdit = [];
    public $comentarios;

    public function editarPedido($pedidoId)
    {
        $this->pedidoEnEdicion = Pedido::with([
            "cliente",
            "pedidoDetalles.producto", // Incluir la relación con producto
        ])->find($pedidoId);

        $this->comentarios = $this->pedidoEnEdicion->comentario;

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
            logger("Productos encontrados:", [
                "lista_precio" => $this->pedidoEnEdicion->lista_precio,
                "productos" => $this->productos->map(function ($producto) {
                    return [
                        "id" => $producto->id,
                        "name" => $producto->name,
                        "precio" => $producto->listaPrecios->first()?->pivot
                            ?->precio,
                    ];
                }),
            ]);
        } else {
            $this->productos = [];
        }
    }

    public function agregarProducto($productoId)
    {
        try {
            DB::beginTransaction();

            $producto = Producto::with([
                "listaPrecios" => function ($query) {
                    $query->where(
                        "lista_precio_id",
                        $this->pedidoEnEdicion->lista_precio
                    );
                },
            ])->find($productoId);

            if (!$producto) {
                throw new \Exception("Producto no encontrado");
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

                $nuevoImporte = $this->calcularImporteDetalle(
                    $nuevaCantidad,
                    $precio,
                    $producto->cantidad
                );

                $detalleExistente->update([
                    "cantidad" => $nuevaCantidad,
                    "importe" => $nuevoImporte,
                ]);

                // Actualizar detallesEdit para el producto existente
                $this->detallesEdit[$detalleExistente->id] = [
                    "cantidad" => $nuevaCantidad,
                    "importe" => $nuevoImporte,
                ];
            } else {
                // Si no existe, crear nuevo detalle
                $cantidad = $producto->cantidad == 1 ? 1 : 0.01;
                $importe =
                    $producto->cantidad == 1
                    ? $precio
                    : $precio / $producto->cantidad;

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
                    ]);

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
        } catch (\Exception $e) {
            DB::rollBack();
            logger("Error al agregar producto:", [
                "error" => $e->getMessage(),
                "stack" => $e->getTraceAsString(),
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

            // Eliminar el detalle
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
        } catch (\Exception $e) {
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
        //dd($this->cambiosTemporales);
        try {
            DB::beginTransaction();
            // Aplicar los cambios temporales
            foreach ($this->cambiosTemporales as $detalleId => $cambio) {
                $detalle = PedidoDetalle::find($detalleId);
                $detalle->update([
                    "cantidad" => $cambio["cantidad"],
                    "importe" => $cambio["importe"],
                ]);
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
        } catch (\Exception $e) {
            DB::rollBack();
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
            $producto = Producto::find($detalle->producto_id);

            // Procesa la cantidad según el tipo de producto
            if ($producto->cantidad == 1) {
                $cantidad = floor($nuevaCantidad);
                $importe = $cantidad * $detalle->producto_precio;
            } else {
                $cantidad = $this->ajustarCantidadDetalle(
                    $nuevaCantidad,
                    $producto->cantidad
                );
                $importe = $this->calcularImporteDetalle(
                    $cantidad,
                    $detalle->producto_precio,
                    $producto->cantidad
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
        $cantidadPorCaja
    ) {
        // Separar cajas y paquetes
        list($cajas, $paquetes) = explode(".", $cantidad);
        $cajas = intval($cajas);
        $paquetes = intval($paquetes);

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
            $this->pedidoEnEdicion->pedidoDetalles()->delete();

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
            $this->dispatch("notify", [
                "message" => "Error al eliminar el pedido: " . $e->getMessage(),
                "type" => "error",
            ]);
        }
    }
}
