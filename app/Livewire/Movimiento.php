<?php

namespace App\Livewire;

use App\Models\Almacen;
use App\Models\Empleado;
use App\Models\Movimiento as ModelsMovimiento;
use App\Models\Producto;
use App\Models\TipoMovimiento;
use App\Models\Vehiculo;
use App\Traits\StockTrait;
use Exception;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use ParseError;

class Movimiento extends Component
{
    use StockTrait;

    public $almacenes;
    public $tipoMovimientos;
    public $conductores;
    public $vehiculos;
    public $detalles;
    public $user;
    public $f_sede_id;
    public $productos;
    public $search;
    public $lista_precio;
    public $importe_total;

    public $almacen_id;
    public $tipo_movimiento_id;
    public $fecha_movimiento;
    public $conductor_id;
    public $vehiculo_id;
    public $fecha_liquidacion;
    public $comentario;
    public $tipo_movimiento_name;
    public $empleado_id;

    public $datos_liquidacion;

    public function mount()
    {
        $this->user = auth_user();
        $this->f_sede_id = $this->user->user_empleado->empleado->f_sede_id;

        $sedes_id = $this->user->user_empleado->empleado->fSede->empresa->sedes->pluck('id');
        $this->almacenes = Almacen::whereIn('f_sede_id', $sedes_id)->get();
        if ($this->almacenes->count() == 1) {
            $this->almacen_id = $this->almacenes->first()->id;
        }
        $this->tipoMovimientos = TipoMovimiento::all();
        $this->conductores = Empleado::where('tipo_empleado', 'conductor')->where('f_sede_id', $this->f_sede_id)->get();
        //dd($this->conductores);
        $this->vehiculos = Vehiculo::all();
        $this->detalles = [];
        $this->fecha_movimiento = date('Y-m-d');
        $this->fecha_liquidacion = date('Y-m-d');
        $this->datos_liquidacion = false;
        $this->empleado_id = $this->user->user_empleado->empleado_id;
    }

    public function updatedTipoMovimientoId()
    {
        $this->tipo_movimiento_name = $this->tipoMovimientos->find($this->tipo_movimiento_id)->name;
        $tipo_movimiento_codigo = $this->tipoMovimientos->find($this->tipo_movimiento_id)->codigo;
        $this->datos_liquidacion = false;
        if ($tipo_movimiento_codigo == '101' || $tipo_movimiento_codigo == '201') {
            $this->datos_liquidacion = true;
        }
    }

    public function updatedSearch()
    {
        $this->lista_precio = 1;
        if (!$this->lista_precio) {
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
                    "listaPrecios" => function ($query) {
                        $query->where("lista_precio_id", $this->lista_precio);
                    },
                    "almacenProductos" => function ($query) {
                        $query->whereIn("almacen_id", $this->almacenes->pluck("id"));
                    },
                ])
                ->take(15)
                ->get();

            // Debug para verificar los precios
            //logger("Productos encontrados:", [
            //    "lista_precio" => $this->lista_precio,
            //    "productos" => $this->productos->map(function ($producto) {
            //        return [
            //            "id" => $producto->id,
            //            "name" => $producto->name,
            //            "precio" => $producto->listaPrecios->first()?->pivot?->precio,
            //        ];
            //    }),
            //]);
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
                "factor" => $producto->cantidad,
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

    public function actualizarCantidad($index)
    {
        $detalle = $this->detalles[$index];
        $producto = Producto::withTrashed()->find($detalle["producto_id"]);

        $precio =
            $producto
            ->listaPrecios()
            ->where("lista_precio_id", $this->lista_precio)
            ->first()->pivot->precio ?? 0;

        $this->detalles[$index]["precio_venta_total"] = $precio * $this->detalles[$index]["cantidad"];
        $this->detalles[$index]["costo_total"] = $precio * $this->detalles[$index]["cantidad"];
        $this->calcularTotal();
    }

    public function calcularImporte($index)
    {
        $detalle = $this->detalles[$index];
        $producto = Producto::withTrashed()->find($detalle['producto_id']);

        if ($producto) {
            $precioCaja = $producto->listaPrecios->where('id', $this->lista_precio)->first()->pivot->precio ?? 0;
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
            $digitos = calcular_digitos($cantidadProducto); // Obtener los dígitos decimales según la cantidad del producto
            // Separar la cantidad en cajas y paquetes
            $cajas = floor($cantidad); // Parte entera representa las cajas
            $paquetes = round(($cantidad - $cajas) * (10 ** $digitos)); // Parte decimal convertida a paquetes

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

    private function calcularTotal()
    {
        $this->importe_total = collect($this->detalles)->sum("importe");
    }

    public function guardarMovimiento()
    {
        $array_validate = [
            "almacen_id" => "required|exists:almacens,id",
            "tipo_movimiento_id" => "required|exists:tipo_movimientos,id",
            "fecha_movimiento" => "required|date",
            "detalles" => "required",
        ];
        if ($this->datos_liquidacion) {
            $array_validate["conductor_id"] = "required|exists:empleados,id";
            $array_validate["vehiculo_id"] = "required|exists:vehiculos,id";
            $array_validate["fecha_liquidacion"] = "required|date";
        }
        $this->validate($array_validate);

        try {
            Cache::lock('generar_movimiento', 15)->block(10, function () {
                DB::transaction(function () {
                    $movimiento = ModelsMovimiento::create($this->all());
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

    // Agregar este método para mantener actualizado el total
    public function updatedDetalles()
    {
        $this->calcularTotal();
    }

    public function ajustarCantidad($index)
    {
        $detalle = $this->detalles[$index];
        $digitos = calcular_digitos($detalle['factor']);
        $cantidad = number_format($detalle['cantidad'], $digitos, '.', '');

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
        $this->detalles[$index]['cantidad'] = number_format($cajas + ($paquetes / (10 ** $digitos)), $digitos, '.', ''); // Convertir de nuevo a formato X.Y

        // Recalcular el importe
        $this->calcularImporte($index);
    }

    public function render()
    {
        return view('livewire.movimiento');
    }
}
