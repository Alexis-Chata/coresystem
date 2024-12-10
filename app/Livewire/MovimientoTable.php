<?php

namespace App\Livewire;

use App\Models\Movimiento;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class MovimientoTable extends PowerGridComponent
{
    public string $tableName = 'movimiento-table-nhmf8n-table';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Movimiento::query()->with('almacen', 'tipoMovimiento', 'conductor', 'vehiculo', 'empleado');
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('almacen_id', fn(Movimiento $model) => $model->almacen->name)
            ->add('tipo_movimiento_id', fn(Movimiento $model) => $model->tipoMovimiento->codigo)
            ->add('tipo_movimiento_name')
            ->add('fecha_movimiento_formatted', fn(Movimiento $model) => Carbon::parse($model->fecha_movimiento)->format('d/m/Y'))
            ->add('conductor_id', fn(Movimiento $model) => optional($model->conductor)->name)
            ->add('vehiculo_id', fn(Movimiento $model) => $model->vehiculo ? $model->vehiculo->id . ' - ' . $model->vehiculo->marca . ' - ' . $model->vehiculo->modelo : '')
            ->add('nro_doc_liquidacion')
            ->add('fecha_liquidacion_formatted', fn(Movimiento $model) => Carbon::parse($model->fecha_liquidacion)->format('d/m/Y'))
            ->add('comentario')
            ->add('empleado_id', fn(Movimiento $model) => $model->empleado->name)
            ->add('created_at_formatted', fn(Movimiento $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'))
            ->add('created_at');
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Almacen', 'almacen_id'),
            Column::make('Tipo', 'tipo_movimiento_id'),
            Column::make('Tipo movimiento name', 'tipo_movimiento_name')
                ->sortable()
                ->searchable(),

            Column::make('Fecha movimiento', 'fecha_movimiento_formatted', 'fecha_movimiento')
                ->sortable(),

            Column::make('Conductor', 'conductor_id'),
            Column::make('Vehiculo', 'vehiculo_id'),
            Column::make('Nro doc liquidacion', 'nro_doc_liquidacion')
                ->sortable()
                ->searchable(),

            Column::make('Fecha liquidacion', 'fecha_liquidacion_formatted', 'fecha_liquidacion')
                ->sortable(),


            Column::make('Comentario', 'comentario')
                ->sortable()
                ->searchable(),

            Column::make('Empleado', 'empleado_id'),
            Column::make('Creado', 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [
            Filter::datepicker('fecha_movimiento'),
            Filter::datepicker('fecha_liquidacion'),
        ];
    }

    #[\Livewire\Attributes\On('anular')]
    public function anular($rowId): void
    {
        $this->js('alert(' . $rowId . ')');
    }

    public function actions(Movimiento $row): array
    {
        return [
            Button::add('anular')
                ->slot('Anular: ' . $row->id)
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('anular', ['rowId' => $row->id])
        ];
    }

    /*
    public function actionRules($row): array
    {
       return [
            // Hide button anular for ID 1
            Rule::button('anular')
                ->when(fn($row) => $row->id === 1)
                ->hide(),
        ];
    }
    */
}
