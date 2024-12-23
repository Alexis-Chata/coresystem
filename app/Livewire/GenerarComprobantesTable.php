<?php

namespace App\Livewire;

use App\Models\FComprobanteSunat;
use App\Models\FSerie;
use App\Models\Movimiento;
use App\Models\Vehiculo;
use App\Traits\CalculosTrait;
use Exception;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Luecano\NumeroALetras\NumeroALetras;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class GenerarComprobantesTable extends PowerGridComponent
{
    use CalculosTrait;
    public string $tableName = 'generar-comprobantes-table-gnkwmv-table';
    public $fecha_reparto;
    public $user;
    public $series;
    public $serie_facturas, $serie_factura_seleccionada;
    public $serie_boletas, $serie_boleta_seleccionada;
    public $serie_nota_pedidos, $serie_nota_pedido_seleccionada;

    public function setUp(): array
    {
        $this->showCheckBox();
        $this->fecha_reparto = Carbon::now();

        if ($this->fecha_reparto->isSaturday()) {
            $this->fecha_reparto = $this->fecha_reparto->addDays(2); // Agregar 2 días si es sábado
        } else {
            $this->fecha_reparto = $this->fecha_reparto->addDay(); // Agregar 1 día en otros casos
        }

        $this->fecha_reparto = $this->fecha_reparto->format("Y-m-d");
        $this->user = auth_user();
        $this->series = $this->user->user_empleado->empleado->fSede->fSeries;
        $this->serie_facturas = $this->series->where('f_tipo_comprobante_id', 2);
        $this->serie_boletas = $this->series->where('f_tipo_comprobante_id', 3);
        $this->serie_nota_pedidos = $this->series->where('f_tipo_comprobante_id', 1);
        $this->serie_factura_seleccionada = $this->serie_facturas->first()->id;
        $this->serie_boleta_seleccionada = $this->serie_boletas->first()->id;
        $this->serie_nota_pedido_seleccionada = $this->serie_nota_pedidos->first()->id;
        //dd($this->serie_nota_pedidos);

        return [
            PowerGrid::header()
                ->includeViewOnTop(
                    "components.fecha-filter"
                )
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Movimiento::query()->with(['tipoMovimiento', 'conductor.fSede', 'almacen'])->where('fecha_movimiento', $this->fecha_reparto)->where('tipo_movimiento_id', 7)->whereIn('estado', ['facturas_por_generar']);
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        $Options = Vehiculo::selectRaw("*, CONCAT(id, ' - ', modelo, ' - ', placa) AS descripcion")->get();
        $Options = $Options->pluck('descripcion', 'id');

        return PowerGrid::fields()
            ->add('id')
            ->add('almacen_id', fn(Movimiento $model) => ($model->almacen->id . " - " . $model->almacen->name))
            ->add('tipo_movimiento_id')
            ->add('fecha_movimiento_formatted', fn(Movimiento $model) => Carbon::parse($model->fecha_movimiento)->format('d/m/Y'))
            ->add('conductor_id', fn(Movimiento $model) => (optional($model->conductor)->id . " - " . optional($model->conductor)->name))
            ->add('vehiculo_id', function ($model) use ($Options) {
                return $this->selectComponent('vehiculo_id', $model->id, $model->vehiculo_id, $Options);
            })
            ->add('nro_doc_liquidacion')
            ->add('fecha_liquidacion_formatted', fn(Movimiento $model) => Carbon::parse($model->fecha_liquidacion)->format('d/m/Y'))
            ->add('comentario')
            ->add('tipo_movimiento_name', fn(Movimiento $model) => ($model->tipoMovimiento->codigo . " - " . $model->tipoMovimiento->name))
            ->add('empleado_id', fn(Movimiento $model) => ($model->empleado->id . " - " . $model->empleado->name))
            ->add('created_at_formatted', fn($model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'))
            ->add('created_at');
    }

    private function selectComponent($field, $rowId, $selected, $options)
    {
        return Blade::render(
            '<select wire:change="updateField(\'' . $field . '\', $event.target.value, ' . $rowId . ')">'
                . '@foreach($options as $value => $label)'
                . '<option value="{{ $value }}" {{ $value == $selected ? \'selected\' : \'\' }}>'
                . '{{ $label }}'
                . '</option>'
                . '@endforeach'
                . '</select>',
            ['options' => $options, 'selected' => $selected]
        );
    }

    #[On('updateField')]
    public function updateField($field, $value, $rowId)
    {
        $categoria = Movimiento::find($rowId);
        if ($categoria) {
            $categoria->update([$field => $value]);
            $this->dispatch('pg:eventRefresh-default');
        }
    }

    public function columns(): array
    {
        return [
            Column::make('No Mov.', 'id'),
            Column::make('Fecha movimiento', 'fecha_movimiento_formatted', 'fecha_movimiento')
                ->sortable(),

            Column::make('Conductor', 'conductor_id'),
            Column::make('Vehiculo', 'vehiculo_id'),

            Column::make('Fecha liquidacion', 'fecha_liquidacion_formatted', 'fecha_liquidacion')
                ->sortable(),

            Column::make('Comentario', 'comentario')
                ->sortable()
                ->searchable(),

            Column::make('Tipo movimiento name', 'tipo_movimiento_name')
                ->sortable()
                ->searchable(),

            Column::make('Almacen id', 'almacen_id'),
            Column::make('Creado por', 'empleado_id'),
            Column::make('Created at', 'created_at_formatted', 'created_at')
                ->sortable(),

            //Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [
            //Filter::datepicker('fecha_movimiento'),
            //Filter::datepicker('fecha_liquidacion'),
        ];
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert(' . $rowId . ')');
    }

    /*
    public function actionRules($row): array
    {
        return [
            // Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn($row) => $row->id === 1)
                ->hide(),
        ];
    }
    */

    public function generar_comprobantes()
    {
        $this->validate([
            'fecha_reparto' => 'required',
            'serie_factura_seleccionada' => 'required',
            'serie_boleta_seleccionada' => 'required',
            'serie_nota_pedido_seleccionada' => 'required',
            'checkboxValues' => 'required'
        ]);

        try {
            Cache::lock('generar_movimiento', 15)->block(10, function () {
                DB::beginTransaction();
                $movimientos = Movimiento::with(['pedidos.pedidoDetalles', 'pedidos.tipoComprobante', 'pedidos.cliente.tipoDocumento'])->whereIn('id', $this->checkboxValues)->get();
                //dd($movimientos);
                foreach ($movimientos as $movimiento) {
                    $movimiento->estado = 'por liquidar';
                    $movimiento->save();
                    $sede = $movimiento->almacen->sede;
                    //dd($sede_id);
                    foreach ($movimiento->pedidos as $pedido) {
                        $pedido->estado = 'facturado';
                        $pedido->save();
                        $cliente = $pedido->cliente;
                        $serie = match ($pedido->tipoComprobante->tipo_comprobante) {
                            '00' => FSerie::find($this->serie_nota_pedido_seleccionada),
                            '01' => FSerie::find($this->serie_factura_seleccionada),
                            '03' => FSerie::find($this->serie_boleta_seleccionada),
                        };
                        $serie->correlativo = $serie->correlativo + 1;
                        $serie->save();
                        $detallesDivididos = $pedido->pedidoDetalles->chunk(3);
                        //dd($detallesDivididos);

                        foreach ($detallesDivididos as $lote) {
                            $formatter = new NumeroALetras();
                            list($subtotales, $detalles) = ($this->setSubTotalesIgv($lote, true));
                            $subtotales = (object)$subtotales;
                            $datos_comprobante = [
                                'ruta_id' => $pedido->ruta_id,
                                'vendedor_id' => $pedido->vendedor_id,
                                'conductor_id' => $pedido->conductor_id,
                                'cliente_id' => $pedido->cliente_id,
                                'movimiento_id' => $pedido->movimiento_id,
                                'sede_id' => $sede->id,
                                'ublVersion' => '2.1',
                                'tipoDoc' => $pedido->tipoComprobante->tipo_comprobante,
                                'tipoDoc_name' => $pedido->tipoComprobante->name,
                                'tipoOperacion' => '0101',
                                'serie' => $serie->serie,
                                'correlativo' => $serie->correlativo,
                                'fechaEmision' => $this->fecha_reparto,
                                'formaPagoTipo' => 'Contado',
                                'tipoMoneda' => 'PEN',
                                'companyRuc' => $sede->empresa->ruc,
                                'companyRazonSocial' => $sede->empresa->razon_social,
                                'companyNombreComercial' => $sede->empresa->name_comercial,
                                'companyAddressUbigueo' => $sede->ubigueo,
                                'companyAddressDepartamento' => $sede->departamento,
                                'companyAddressProvincia' => $sede->provincia,
                                'companyAddressDistrito' => $sede->distrito,
                                'companyAddressUrbanizacion' => $sede->urbanizacion ?? null,
                                'companyAddressDireccion' => $sede->direccion,
                                'companyAddressCodLocal' => $sede->addresstypecode,
                                'clientTipoDoc' => $cliente->tipoDocumento->codigo,
                                'clientNumDoc' => $cliente->numero_documento,
                                'clientRazonSocial' => $cliente->razon_social,
                                'mtoOperGravadas' => $subtotales->mtoOperGravadas,
                                'mtoOperInafectas' => $subtotales->mtoOperInafectas,
                                'mtoOperExoneradas' => $subtotales->mtoOperExoneradas,
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
                                'tipDocAfectado' => null,
                                'numDocfectado' => null,
                                'codMotivo' => null,
                                'desMotivo' => null,
                                'nombrexml' => null,
                                'xmlbase64' => null,
                                'hash' => null,
                                'cdrbase64' => null,
                                'codigo_sunat' => null,
                                'mensaje_sunat' => null,
                                'obs' => null,
                                'empresa_id' => $pedido->empresa_id,

                                'pedido_id' => $pedido->id,
                                'fecha' => now(),
                                // Otros campos necesarios
                            ];

                            $invoice = FComprobanteSunat::create($datos_comprobante);
                            $invoice->detalle()->createMany($detalles->toArray());
                        }
                    }
                }
                DB::commit();
            });
        } catch (Exception | LockTimeoutException $e) {
            DB::rollback();
            logger("Error al guardar movimiento:", ["error" => $e->getMessage()]);
            //throw $e; // Relanza la excepción si necesitas propagarla
            $this->dispatch("error-guardando-movimiento", "Error al guardar el movimiento" . "<br>" . $e->getMessage());
            $this->addError("error_guardar", $e->getMessage());
        }

        $this->checkboxValues = [];
        $this->resetValidation();
        $this->dispatch("actualizar_fecha_reparto", $this->fecha_reparto)->to(MovimientoComprobanteGeneradosTable::class);
    }

    public function updatedFechaReparto(): void
    {
        $this->dispatch("actualizar_fecha_reparto", $this->fecha_reparto)->to(MovimientoComprobanteGeneradosTable::class);
    }
}
