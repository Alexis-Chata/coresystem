<?php

namespace App\Livewire;

use App\Models\Almacen;
use App\Models\Empleado;
use App\Models\Marca;
use App\Models\Movimiento;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Producto;
use App\Models\TipoMovimiento;
use App\Traits\StockTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class GenerarMovimientoLiquido extends Component
{
    use StockTrait;
    public $fecha_reparto;
    public $fecha_liquidacion;
    public $pedidosAgrupados;
    public $conductores;
    public $checkbox_conductor_seleccionados = [];
    public $cargas_generadas = [];

    public function mount()
    {
        $this->conductores = Empleado::where('tipo_empleado', 'conductor')->get();
        $this->fecha_reparto = Carbon::now();

        if ($this->fecha_reparto->isSaturday()) {
            $this->fecha_reparto = $this->fecha_reparto->addDays(2); // Agregar 2 días si es sábado
        } else {
            $this->fecha_reparto = $this->fecha_reparto->addDay(); // Agregar 1 día en otros casos
        }

        $this->fecha_reparto = $this->fecha_reparto->format("Y-m-d");
        $this->fecha_liquidacion = $this->fecha_reparto;
        $this->pedidosAgrupados = $this->pedidos_agrupados();
        $this->cargas_generadas = $this->movimientos_generados();
    }

    public function updatedFechaReparto($value)
    {
        $this->fecha_liquidacion = $this->fecha_reparto;
        $this->pedidosAgrupados = $this->pedidos_agrupados();
        $this->cargas_generadas = $this->movimientos_generados();
        //dd($this->fecha_reparto, $this->fecha_liquidacion);
    }

    public function render()
    {
        return view('livewire.generar-movimiento-liquido');
    }

    public function pedidos_agrupados()
    {
        return Pedido::with('conductor')->where('fecha_reparto', $this->fecha_reparto)
            ->where('estado', 'asignado')
            ->select(
                'conductor_id',
                DB::raw('SUM(importe_total) as total_importe'),
                DB::raw('COUNT(DISTINCT cliente_id) as total_clientes')
            )
            ->groupBy('conductor_id')
            ->get();
    }

    public function movimientos_generados()
    {
        return Movimiento::query()->with(['tipoMovimiento', 'conductor.fSede', 'almacen', 'pedidos'])->where('fecha_liquidacion', $this->fecha_reparto)
            ->whereHas('tipoMovimiento', function ($query) {
                $query->where('codigo', 201); // Filtrar por el código en la relación 'tipoMovimiento'
            })->get();
    }

    public function generar_movimiento()
    {
        $this->pedidosAgrupados = $this->pedidos_agrupados();
        $this->validate([
            'fecha_reparto' => 'required|date',
        ]);
        if (empty($this->checkbox_conductor_seleccionados)) {
            $this->addError('checkbox_conductor_seleccionados', 'Debe seleccionar al menos un conductor.');
            return;
        }
        try {
            Cache::lock('generar_movimiento', 15)->block(10, function () {
                DB::beginTransaction();
                $fecha_reparto = $this->fecha_reparto;
                $productos = Producto::withTrashed()->with('marca')->get();
                foreach ($this->checkbox_conductor_seleccionados as $conductor_id) {

                    $pedidos = Pedido::lockForUpdate()->where('conductor_id', $conductor_id)->where('fecha_reparto', $fecha_reparto)->where('estado', 'asignado')->get("id");
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

                    $user = auth_user();
                    $sedes_id = $user->user_empleado->empleado->fSede->empresa->sedes->pluck('id');
                    $almacenes = Almacen::whereIn('f_sede_id', $sedes_id)->get();
                    $almacen_id = $almacenes->first()->id;
                    $conductor = Empleado::find($conductor_id);
                    $tipo_movimiento = TipoMovimiento::firstWhere("codigo", "201"); //sal. reparto sujeta a liquidacion

                    $data_para_movimiento_detalle = $pedidos_detalle->map(function ($item) use ($user) {
                        return [
                            'producto_id' => $item->producto_id,
                            'producto_name' => $item->producto_name,
                            'producto_cantidad' => $item->cantidad,
                            'marca_id' => $item->marca_id,
                            'marca' => $item->marca,
                            'totalqcanpedbultos' => $item->totalqcanpedbultos,
                            'totalqcanpedunidads' => $item->totalqcanpedunidads,
                            'cantidad' => number_format_punto2($item->totalqcanpedbultos + ($item->totalqcanpedunidads / 100)),
                            'producto_precio_venta' => number_format_punto2($item->precio),
                            'precio_venta_unitario' => number_format_punto2($item->precio / $item->cantidad),
                            'precio_venta_total' => number_format_punto2($item->importe),
                            "costo_unitario" => number_format_punto2($item->precio / $item->cantidad),
                            "costo_total" => number_format_punto2($item->importe),
                            'empleado_id' => $user->user_empleado->empleado->id,
                        ];
                    })->toArray();

                    // Ordenar por marca_id y luego por producto_id
                    usort($data_para_movimiento_detalle, function ($a, $b) {
                        return $a['marca_id'] <=> $b['marca_id'] ?: $a['producto_id'] <=> $b['producto_id'];
                    });

                    // consolidar detalle para generar movimiento

                    $data_para_movimiento = [
                        "almacen_id" => $almacen_id,
                        "tipo_movimiento_id" => $tipo_movimiento->id,
                        "fecha_movimiento" => now(),
                        "conductor_id" => $conductor->id,
                        "vehiculo_id" => $conductor->vehiculo_id,
                        "nro_doc_liquidacion" => null,
                        "fecha_liquidacion" => $fecha_reparto,
                        "tipo_movimiento_name" => $tipo_movimiento->name,
                        'empleado_id' => $user->user_empleado->empleado->id,
                        "estado" => "facturas_por_generar",
                    ];

                    //dd($pedidos_id, $pedidos_detalle, $data_para_movimiento, $data_para_movimiento_detalle);
                    $movimiento = Movimiento::create($data_para_movimiento);
                    $movimiento->nro_doc_liquidacion = $movimiento->id;
                    $movimiento->save();
                    $movimiento->movimientoDetalles()->createMany($data_para_movimiento_detalle);
                    $this->actualizarStock($movimiento);

                    $pedidos->each(function ($pedido) use ($movimiento) {
                        $pedido->estado = "movimiento-generado";
                        $pedido->movimiento_id = $movimiento->id;
                        $pedido->save();
                    });
                }
                $this->checkbox_conductor_seleccionados = [];
                $this->pedidosAgrupados = $this->pedidos_agrupados();
                DB::commit();
            });
        } catch (Exception | LockTimeoutException $e) {
            DB::rollback();
            logger("Error al guardar movimiento:", ["error" => $e->getMessage()]);
            //throw $e; // Relanza la excepción si necesitas propagarla
            $this->dispatch("error-guardando-movimiento", "Error al guardar el movimiento" . "<br>" . $e->getMessage());
            $this->addError("error_guardar", $e->getMessage());
        }
        $this->cargas_generadas = $this->movimientos_generados();
    }

    public function exportarMovimientoCargaPDF(Movimiento $movimiento)
    {
        // Ruta donde esta/guardará el archivo
        $file_name = 'movimiento_carga_'.$movimiento->id.'.pdf';
        $filePath = 'cola-pdfs/'.$file_name;

        if (Storage::disk('local')->exists($filePath)) {
            return response()->download(storage_path("app/private/$filePath"));
        }
        $marca = Marca::all();
        $movimiento->load([
            'movimientoDetalles.producto' => function ($query) {
                $query->withTrashed(); // Incluir productos eliminados
            },
            'movimientoDetalles.producto.marca',
            'tipoMovimiento',
            'conductor.fSede',
            'almacen',
            'vehiculo'
        ]);
        $detallesAgrupados = $movimiento->movimientoDetalles->groupBy(function ($detalle) {
            return $detalle->producto->marca->id; // Agrupar por nombre de la marca
        });

        $detallesAgrupadosOrdenados = $detallesAgrupados->sortBy(function ($grupo, $marcaId) {
            return optional($grupo->first()->producto->marca)->nro_orden;
        });
        //dd($movimiento->movimientoDetalles->toArray(), $detallesAgrupadosOrdenados->first()->first()->cantidad_bultos);
        // Generar el PDF
        $pdf = Pdf::loadView(
            "pdf.movimiento-carga",
            compact("movimiento", "detallesAgrupadosOrdenados", "marca")
        );

        // Guardar el PDF en storage/app/private
        Storage::disk('local')->put($filePath, $pdf->output());

        // Descargar el PDF
        return response()->streamDownload(
            fn() => print $pdf->output(), $file_name
        );
    }
}
