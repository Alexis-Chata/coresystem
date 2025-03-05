<?php

namespace App\Traits;

use App\Models\Empleado;
use App\Models\Movimiento;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Support\Facades\Cache;
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

    public function validarPrecio_arraydetalles($array_detalles, $almacen_id)
    {
        $array_detalles = collect($array_detalles);
        $errores = [];
        $mesaje_error = "";
        $ids_productos = $array_detalles->pluck('producto_id')->toArray();
        $productos = Producto::with(['almacenProductos'])->whereIn('id', $ids_productos)->get();
        //dd($productos, $array_detalles);

        foreach ($array_detalles as $detalle) {
            $producto = $productos->find($detalle['producto_id']);

            // Comparar el stock disponible con la cantidad solicitada
            if ($detalle['importe'] == 0 and $producto->f_tipo_afectacion_id != '21') {
                $errores[] = [
                    'producto_id' => $detalle['producto_id'],
                    'nombre' => $detalle['nombre'],
                    'importe' => $detalle['importe'],
                ];
            }
        }

        // Mostrar errores si hay productos sin suficiente stock
        if (!empty($errores)) {
            foreach ($errores as $error) {
                $mesaje_error .= "Producto: ({$error['producto_id']}) {$error['nombre']}<br />";
                $mesaje_error .= "Importe no puede ser: {$error['importe']}<br />";
                $mesaje_error .= "-----------<br />";
            }
            throw new \Exception("Importe no validos:<br />" . $mesaje_error);
            //logger("Lanzando excepción por stock insuficiente.");
        } else {
            $mesaje_error .= "Todos los productos tienen suficiente stock.<br />";
        }
    }

    public function actualizarStock(Movimiento|Pedido $movimiento_or_pedido, $anulando = false)
    {
        Cache::lock('actualizar_stock', 15)->block(10, function () use ($movimiento_or_pedido, $anulando) {
            if ($movimiento_or_pedido instanceof Movimiento) {
                $this->movimientoStock($movimiento_or_pedido, $anulando);
            } elseif ($movimiento_or_pedido instanceof Pedido) {
                $this->pedidoStock($movimiento_or_pedido, $anulando);
            }
        });
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
            $producto = Producto::withTrashed()->find($detalle->producto_id);
            logger("movimientoStock:", ["producto_id" => $detalle->producto_id]);
            $almacenProducto = $producto->almacenProductos()->where("almacen_id", $movimiento->almacen_id)->first();

            if (!$almacenProducto) {
                $almacenProducto = $producto->almacenProductos()->create(["almacen_id" => $movimiento->almacen_id, "stock_disponible" => 0, "stock_fisico" => 0]);
            }

            if ($tipo_movimiento == 'ingreso') {
                $nuevo_stock_disponible = $this->calculandoNuevoStock($producto, number_format_punto2($almacenProducto->stock_disponible), number_format_punto2($detalle->cantidad), true);
                $nuevo_stock_fisico = $this->calculandoNuevoStock($producto, number_format_punto2($almacenProducto->stock_fisico), number_format_punto2($detalle->cantidad), true);
                $almacenProducto->update(["stock_disponible" => $nuevo_stock_disponible, "stock_fisico" => $nuevo_stock_fisico]);
            }
            if ($tipo_movimiento == 'salida') {
                if ($almacenProducto->stock_disponible < $detalle->cantidad && $codigo_movimiento != '201') {
                    throw new \Exception("Stock insuficiente para el producto {$producto->name}. Stock disponible: {$almacenProducto->stock_disponible}. Solicitado {$detalle->cantidad}");
                }

                $nuevo_stock_fisico = $this->calculandoNuevoStock($producto, number_format_punto2($almacenProducto->stock_fisico), number_format_punto2($detalle->cantidad), false);
                $almacenProducto->update(["stock_fisico" => $nuevo_stock_fisico]);
                if ($codigo_movimiento != '201') {
                    $nuevo_stock_disponible = $this->calculandoNuevoStock($producto, number_format_punto2($almacenProducto->stock_disponible), number_format_punto2($detalle->cantidad), false);
                    $almacenProducto->update(["stock_disponible" => $nuevo_stock_disponible]);
                }
            }
        });
    }

    public function pedidoStock(Pedido $pedido, $anulando = false)
    {
        $almacenId = Empleado::with(['fSede.almacen'])->find($pedido->vendedor_id)->fSede->almacen->id;
        //dd($almacenId);
        $pedido->pedidoDetalles->each(function ($detalle) use ($almacenId, $anulando) {
            $producto = Producto::find($detalle->producto_id);
            $almacenProducto = $producto->almacenProductos()->where("almacen_id", $almacenId)->first();
            $nuevo_stock_disponible = $this->calculandoNuevoStock($producto, number_format_punto2($almacenProducto->stock_disponible), number_format_punto2($detalle->cantidad), $anulando);

            if (!$almacenProducto) {
                $almacenProducto = $producto->almacenProductos()->create(["almacen_id" => $almacenId, "stock_disponible" => 0, "stock_fisico" => 0]);
            }
            //dd($nuevo_stock_disponible);
            if ($almacenProducto->stock_disponible < $detalle->cantidad) {
                throw new \Exception("Stock insuficiente para el producto {$producto->name}. Stock disponible: {$almacenProducto->stock_disponible}. Solicitado {$detalle->cantidad}");
            }

            $almacenProducto->update([
                "stock_disponible" => $nuevo_stock_disponible,
            ]);
        });
    }

    public function calculandoNuevoStock($producto, $stock_disponible, $cantidad_detalle, $anulando)
    {
        list($stock_disponible_entero, $stock_disponible_decimal) = explode('.', $stock_disponible);

        $stock_disponible_display = ($stock_disponible_entero * $producto->cantidad) + $stock_disponible_decimal;

        list($cantidad_detalle_entero, $cantidad_detalle_decimal) = explode('.', $cantidad_detalle);

        $cantidad_detalle_display = ($cantidad_detalle_entero * $producto->cantidad) + $cantidad_detalle_decimal;

        //dd($stock_disponible_display, $cantidad_detalle_display);
        if ($anulando) {
            $nuevo_stock_display = $stock_disponible_display + $cantidad_detalle_display;
        } else {
            $nuevo_stock_display = $stock_disponible_display - $cantidad_detalle_display;
        }
        $nuevo_stock = intval($nuevo_stock_display / $producto->cantidad) + intval($nuevo_stock_display % $producto->cantidad) / 100;
        return $nuevo_stock;
    }

}
