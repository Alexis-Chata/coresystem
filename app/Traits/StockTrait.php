<?php

namespace App\Traits;

use App\Models\Movimiento;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

trait StockTrait
{
    public function validarStock_arraydetalles($array_detalles, $almacen_id)
    {
        $array_detalles = collect($array_detalles);
        // Variable para almacenar productos sin suficiente stock
        $errores = [];
        $mesaje_error = "";
        $ids_productos = $array_detalles->pluck('producto_id')->toArray();
        $stock_productos = Producto::with(['almacenProductos'])->whereIn('id', $ids_productos)->get();
        //dd($stock_productos, $array_detalles->pluck(['producto_id']));

        foreach ($array_detalles as $detalle) {
            // Obtener el stock actual del producto desde la base de datos
            $producto_stock = $stock_productos->find($detalle['producto_id'])->almacenProductos->firstWhere('almacen_id', $almacen_id);
            $stock_disponible = optional($producto_stock)->stock_disponible ?? 0;

            // Comparar el stock disponible con la cantidad solicitada
            if ($stock_disponible < $detalle['cantidad']) {
                $errores[] = [
                    'producto_id' => $detalle['producto_id'],
                    'nombre' => $detalle['nombre'],
                    'stock_disponible' => $stock_disponible,
                    'cantidad_solicitada' => $detalle['cantidad'],
                ];
            }
        }

        // Mostrar errores si hay productos sin suficiente stock
        if (!empty($errores)) {
            foreach ($errores as $error) {
                $mesaje_error .= "Producto: {$error['nombre']} ({$error['producto_id']})<br />";
                $mesaje_error .= "Stock disponible: {$error['stock_disponible']} | Cantidad solicitada: {$error['cantidad_solicitada']}<br />";
                $mesaje_error .= "-----------<br />";
            }
            throw new \Exception("Stock insuficiente:<br />" . $mesaje_error);
            //logger("Lanzando excepción por stock insuficiente.");
        } else {
            $mesaje_error .= "Todos los productos tienen suficiente stock.<br />";
        }
    }

    public function actualizarStock(Movimiento|Pedido $movimiento_or_pedido, $anulando = false)
    {
        if ($movimiento_or_pedido instanceof Movimiento) {
            $this->movimientoStock($movimiento_or_pedido, $anulando);
        } elseif ($movimiento_or_pedido instanceof Pedido) {
            $this->pedidoStock($movimiento_or_pedido, $anulando);
        }
    }

    public function movimientoStock(Movimiento $movimiento, $anulando = false)
    {
        $tipo_movimiento = $movimiento->tipoMovimiento->tipo;
        $codigo_movimiento = $movimiento->tipoMovimiento->codigo;
        if ($anulando) {
            $tipo_movimiento = match ($tipo_movimiento) {
                'ingreso' => 'salida',
                'salida' => 'ingreso',
            };
        }

        $movimiento->movimientoDetalles->each(function ($detalle) use ($movimiento, $tipo_movimiento, $codigo_movimiento) {
            $producto = Producto::find($detalle->producto_id);
            $almacenProducto = $producto->almacenProductos()->where("almacen_id", $movimiento->almacen_id)->first();

            if (!$almacenProducto) {
                $almacenProducto = $producto->almacenProductos()->create(["almacen_id" => $movimiento->almacen_id, "stock_disponible" => 0, "stock_fisico" => 0]);
            }

            if ($tipo_movimiento == 'ingreso') {
                $almacenProducto->update(["stock_disponible" => DB::raw("stock_disponible + {$detalle->cantidad}"), "stock_fisico" => DB::raw("stock_fisico + {$detalle->cantidad}")]);
            }
            if ($tipo_movimiento == 'salida') {
                if ($almacenProducto->stock_disponible < $detalle->cantidad) {
                    throw new \Exception("Stock insuficiente para el producto {$producto->nombre}. Stock disponible: {$almacenProducto->stock_disponible}");
                }

                $almacenProducto->update(["stock_fisico" => DB::raw("stock_fisico - {$detalle->cantidad}")]);
                if ($codigo_movimiento != '201') {
                    $almacenProducto->update(["stock_disponible" => DB::raw("stock_disponible - {$detalle->cantidad}")]);
                }
            }
        });
    }

    public function pedidoStock(Pedido $pedido, $anulando = false)
    {
        $almacenId = $pedido->empresa->almacenes->first()->id;
        $pedido->pedidoDetalles->each(function ($detalle) use ($almacenId, $anulando) {
            $producto = Producto::find($detalle->producto_id);
            $almacenProducto = $producto->almacenProductos()->where("almacen_id", $almacenId)->first();

            if (!$almacenProducto) {
                $almacenProducto = $producto->almacenProductos()->create(["almacen_id" => $almacenId, "stock_disponible" => 0, "stock_fisico" => 0]);
            }

            if ($almacenProducto->stock_disponible < $detalle->cantidad) {
                throw new \Exception("Stock insuficiente para el producto {$producto->nombre}. Stock disponible: {$almacenProducto->stock_disponible}");
            }

            $almacenProducto->update([
                "stock_disponible" => DB::raw("stock_disponible - {$detalle->cantidad}"),
            ]);
        });
    }
}
