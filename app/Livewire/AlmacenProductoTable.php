<?php

namespace App\Livewire;

use App\Models\AlmacenProducto;
use App\Models\Empresa;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class AlmacenProductoTable extends PowerGridComponent
{
    public string $tableName = 'almacen-producto-table-qf0adh-table';
    public $empleado;
    public $user;
    public $f_sede;
    public $empresas;

    public function setUp(): array
    {
        $this->showCheckBox();
        $this->user = auth_user();
        $this->empleado = $this->user->empleados()->first();
        $this->f_sede = $this->user->empleados()->first()->FSede->name;
        $this->empresas = Empresa::all();

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
        return AlmacenProducto::query()->with(['producto' => function ($query) {
            $query->withTrashed();
        },'producto.marca', 'almacen']);
    }

    public function relationSearch(): array
    {
        return [
            'producto' => ['name'],
            'almacen' => ['name'],
            'producto.marca' => ['name'],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('producto_id', function ($row) {
                return $row->producto->id;
            })
            ->add('producto_name', function ($row) {
                return $row->producto->name;
            })
            ->add('almacen_id', function ($row) {
                return $row->almacen->name;
            })
            ->add('stock_disponible', function ($row) {
                return number_format_punto2($row->stock_disponible);
            })
            ->add('stock_fisico', function ($row) {
                return number_format_punto2($row->stock_fisico);
            })
            ->add('marca_name', function ($row) {
                return $row->producto->marca->name;
            })
            ->add('created_at_formatted', fn($model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'))
            ->add('created_at');
    }

    public function columns(): array
    {
        return [
            Column::make('Producto id', 'producto_id'),
            Column::make('Producto Name', 'producto_name'),

            Column::make('Stock disponible', 'stock_disponible')
            ->sortable()
            ->searchable(),

            Column::make('Stock fisico', 'stock_fisico')
            ->sortable()
            ->searchable(),

            Column::make('Marca', 'marca_name'),
            Column::make('Almacen', 'almacen_id'),
            Column::make('Created at', 'created_at_formatted', 'created_at')
            ->sortable()
            ->searchable(),

            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [];
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert(' . $rowId . ')');
        $this->dispatch('SweetAlert2', $rowId);
    }

    public function actions(AlmacenProducto $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit: ' . $row->id)
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('edit', ['rowId' => $row->id])
        ];
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
}
