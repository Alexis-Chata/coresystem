<?php

namespace App\Livewire;

use App\Models\Movimiento;
use App\Models\Vehiculo;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Blade;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class GenerarComprobantesTable extends PowerGridComponent
{
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
        $this->fecha_reparto = now()->format("Y-m-d");
        $this->series = Vehiculo::selectRaw("*, CONCAT(id, ' - ', modelo, ' - ', placa) AS descripcion")->get();
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
        return Movimiento::query()->with(['tipoMovimiento', 'conductor.fSede', 'almacen'])->where('fecha_movimiento', $this->fecha_reparto);
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
            ->add('conductor_id', fn(Movimiento $model) => ($model->conductor->id . " - " . $model->conductor->name))
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
            Column::make('Empleado id', 'empleado_id'),
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

        $pedido = Pedido::with('detalles')->findOrFail($idPedido);
        $detallesDivididos = $pedido->detalles->chunk(16);

        $facturas = [];
        foreach ($detallesDivididos as $lote) {
            $factura = Factura::create([
                'pedido_id' => $pedido->id,
                'fecha' => now(),
                // Otros campos necesarios
            ]);

            foreach ($lote as $detalle) {
                FacturaDetalle::create([
                    'factura_id' => $factura->id,
                    'producto_id' => $detalle->producto_id,
                    'cantidad' => $detalle->cantidad,
                    'precio' => $detalle->precio,
                    // Otros campos necesarios
                ]);
            }

            $facturas[] = $factura;
        }

        $this->checkboxValues = [];
    }
}
