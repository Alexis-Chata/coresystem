<?php

namespace App\Livewire;

use App\Models\Almacen;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Producto;
use Exception;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class GenerarMovimientoLiquido extends Component
{
    public $fecha_reparto;
    public $fecha_liquidacion;
    public $pedidosAgrupados;
    public $conductores;
    public $checkbox_conductor_seleccionados = [];

    public function mount()
    {
        $this->conductores = Empleado::where('tipo_empleado', 'conductor')->get();
        $this->fecha_reparto = date('Y-m-d');
        $this->fecha_liquidacion = date('Y-m-d');
        $this->pedidos_agrupados();
    }

    public function updatedFechaReparto($value)
    {
        // Aquí puedes manejar el array de seleccionados, por ejemplo:
        $this->fecha_liquidacion = $this->fecha_reparto;
        $this->pedidos_agrupados();
        //dd($this->fecha_reparto, $this->fecha_liquidacion);
    }

    public function render()
    {
        return view('livewire.generar-movimiento-liquido');
    }

    public function pedidos_agrupados()
    {
        $this->pedidosAgrupados = Pedido::with('conductor')->where('fecha_reparto', $this->fecha_reparto)
            ->where('estado', 'asignado')
            ->select(
                'conductor_id',
                DB::raw('SUM(importe_total) as total_importe'),
                DB::raw('COUNT(DISTINCT cliente_id) as total_clientes')
            )
            ->groupBy('conductor_id')
            ->get();
    }

    public function generar_movimiento()
    {
        try {
            DB::beginTransaction();
            $this->pedidos_agrupados();
            $productos = Producto::with('marca')->get();
            foreach ($this->checkbox_conductor_seleccionados as $value) {
                $pedidos = Pedido::lockForUpdate()->where('conductor_id', $value)->where('fecha_reparto', $this->fecha_reparto)->where('estado', 'asignado')->get("id");
                $pedidos_id = $pedidos->pluck('id');
                $pedidos_detalle = PedidoDetalle::lockForUpdate()->whereIn('pedido_id', $pedidos_id)->get()->groupBy('producto_id');
                $pedidos_detalle->each(function ($item, $key) use ($productos) {
                    $unidadMedida = $productos->find($key)->cantidad;

                    $sumaunidads = $item->sum('qcanpedunidads');
                    $sumaunidadsAbultos = intval($sumaunidads / $unidadMedida);
                    $sumaunidadsAbultosRestoenunidad = $sumaunidads % $unidadMedida;

                    $item->producto_id = $productos->find($key)->id;
                    $item->producto_name = $productos->find($key)->name;
                    $item->marca_id = $productos->find($key)->marca->id;
                    $item->marca = $productos->find($key)->marca->name;
                    $item->totalqcanpedbultos = $item->sum('qcanpedbultos') + $sumaunidadsAbultos;
                    $item->totalqcanpedunidads = str_pad($sumaunidadsAbultosRestoenunidad, 2, 0, STR_PAD_LEFT);

                    $item->cantidad = $productos->find($key)->cantidad;
                    $item->precio = $productos->find($key)->listaPrecios->find(1)->pivot->precio;
                    $importe = ($item->totalqcanpedbultos * $item->precio) + ($item->precio * $item->totalqcanpedunidads) / $item->cantidad;
                    $item->importe = ($importe);
                    //dd($item, $key, $productos->find($key), $sumaunidads);
                });

                $user = auth()->user();
                $f_sede_id = $user->user_empleado->empleado->f_sede_id;

                $sedes_id = $user->user_empleado->empleado->fSede->empresa->sedes->pluck('id');
                $almacenes = Almacen::whereIn('f_sede_id', $sedes_id)->get();
                $almacen_id = $almacenes->first()->id;

                $data_para_movimiento_detalle = $pedidos_detalle->map(function ($item) use ($user){
                    return [
                        'producto_id' => $item->producto_id,
                        'producto_name' => $item->producto_name,
                        'producto_cantidad' => $item->cantidad,
                        'marca_id' => $item->marca_id,
                        'marca' => $item->marca,
                        'totalqcanpedbultos' => $item->totalqcanpedbultos,
                        'totalqcanpedunidads' => $item->totalqcanpedunidads,
                        'cantidad' => number_format_punto2($item->totalqcanpedbultos + ($item->totalqcanpedunidads / 100)),
                        'producto_precio' => number_format_punto2($item->precio),
                        'precio_venta_unitario' => number_format_punto2($item->precio / $item->cantidad),
                        'precio_venta_total' => number_format_punto2($item->importe),
                        'empleado_id' => $user->user_empleado->empleado->id,
                    ];
                })->toArray();

                // consolidar detalle para generar movimiento

                dd($pedidos_id, $pedidos_detalle, $data_para_movimiento_detalle);
            }
            $this->checkbox_conductor_seleccionados = [];
            DB::commit();
        } catch (Exception | LockTimeoutException $e) {
            DB::rollback();
            logger("Error al guardar movimiento:", ["error" => $e->getMessage()]);
            //throw $e; // Relanza la excepción si necesitas propagarla
            $this->dispatch("error-guardando-pedido", "Error al guardar el movimiento" . "<br>" . $e->getMessage());
            $this->addError("error_guardar", $e->getMessage());
        }
    }
}
