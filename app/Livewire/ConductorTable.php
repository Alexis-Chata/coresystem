<?php

namespace App\Livewire;

use App\Models\Conductor;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class ConductorTable extends PowerGridComponent
{
    public string $tableName = 'conductor-table-ldc2rg-table';

    public function setUp(): array
    {
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
        return Conductor::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('codigo')
            ->add('name')
            ->add('direccion')
            ->add('celular')
            ->add('f_tipo_documento_id')
            ->add('numero_documento')
            ->add('tipo_empleado')
            ->add('numero_brevete')
            ->add('created_at');
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Codigo', 'codigo')
                ->sortable()
                ->searchable(),
                
            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Direccion', 'direccion')
                ->sortable()
                ->searchable(),

            Column::make('Celular', 'celular')
                ->sortable()
                ->searchable()
                ->editOnClick(),
                
            Column::make('F tipo documento id', 'f_tipo_documento_id'),
            Column::make('Numero documento', 'numero_documento')
                ->sortable()
                ->searchable(),
                
            Column::make('Tipo empleado', 'tipo_empleado')
                ->sortable()
                ->searchable(),

            Column::make('Numero brevete', 'numero_brevete')
                ->sortable()
                ->searchable(),

            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [
        ];
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        if (auth()->user()->can('edit conductors')) {
            $this->js('alert('.$rowId.')');
        } else {
            $this->addError('unauthorized', 'No tienes permiso para editar este conductor.');
        }
    }

    public function actions(Conductor $row): array
    {
        $actions = [];

        if (auth()->user()->can('edit conductors')) {
            $actions[] = Button::add('edit')
                ->slot('Edit: '.$row->id)
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-primary-700')
                ->dispatch('edit', ['rowId' => $row->id]);
        }

        return $actions;
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
    public function onUpdatedEditable(string|int $id, string $field, string $value): void
    {
        try {
            if (!auth()->user()->can('edit conductors')) {
                $this->addError('unauthorized', 'No tienes permiso para editar este campo.');
                $this->dispatch('showAlert', message: 'No tienes permiso para editar este campo.', type: 'error');
                return;
            }

            $conductor = Conductor::find($id);
            if ($conductor) {
                $conductor->update([$field => $value]);
                $this->dispatch('showAlert', message: 'Campo actualizado con éxito.', type: 'success');
            } else {
                $this->addError('notFound', 'No se encontró el conductor.');
                $this->dispatch('showAlert', message: 'No se encontró el conductor.', type: 'error');
            }
        } catch (\Exception $e) {
            $this->addError('updateError', 'Error al actualizar el campo: ' . $e->getMessage());
            $this->dispatch('showAlert', message: 'Error al actualizar el campo: ' . $e->getMessage(), type: 'error');
        }
    }
}
