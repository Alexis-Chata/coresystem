<?php

namespace App\Livewire;

use App\Models\Empleado;
use App\Models\FTipoDocumento;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use Illuminate\Support\Facades\Blade;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use Livewire\Attributes\On;

final class EmpleadosTable extends PowerGridComponent
{
    public string $tableName = 'empleados-table-4jmu5i-table';

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
        return Empleado::query()->with('tipoDocumento');
    }

    public function fields(): PowerGridFields
    {
        $options = $this->tipoDocumentoSelectOptions();

        return PowerGrid::fields()
            ->add('id')
            ->add('codigo')
            ->add('name')
            ->add('direccion')
            ->add('celular')
            ->add('tipo_documento', function ($empleado) use ($options) {
                return Blade::render('<x-select-tipo-documento :options=$options :empleadoId=$empleadoId :selected=$selected/>', [
                    'options' => $options,
                    'empleadoId' => $empleado->id,
                    'selected' => $empleado->f_tipo_documento_id
                ]);
            })
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
                ->searchable(),
            Column::make('Tipo documento', 'tipo_documento'),
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

    public function tipoDocumentoSelectOptions()
    {
        return FTipoDocumento::all(['id', 'tipo_documento'])->mapWithKeys(function ($item) {
            return [$item->id => $item->tipo_documento];
        });
    }

    #[On('tipoDocumentoChanged')]
    public function tipoDocumentoChanged($tipoDocumentoId, $empleadoId): void
    {
        $empleado = Empleado::find($empleadoId);
        if ($empleado) {
            $empleado->f_tipo_documento_id = $tipoDocumentoId;
            $empleado->save();
        }
    }

    public function filters(): array
    {
        return [
        ];
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert('.$rowId.')');
    }

    public function actions(Empleado $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit: '.$row->id)
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
            if ($field === 'tipo_documento') {
                $empleado = Empleado::find($id);
                if ($empleado) {
                    $empleado->f_tipo_documento_id = $value;
                    $empleado->save();
                }
            }
        } catch (\Exception $e) {
            $this->addError('updateError', 'Error al actualizar el campo: ' . $e->getMessage());
            $this->dispatch('showAlert', message: 'Error al actualizar el campo: ' . $e->getMessage(), type: 'error');
        }
    }
}
