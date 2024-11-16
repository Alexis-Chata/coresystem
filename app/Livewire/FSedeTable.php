<?php

namespace App\Livewire;

use App\Models\Empresa;
use App\Models\FSede;
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

final class FSedeTable extends PowerGridComponent
{
    public string $tableName = 'f-sede-table-oxtrva-table';
    public bool $showCreateForm = false;
    public $empleado;
    public $user;
    public $empresas;
    public $productionSelectOptions = [0 => '0-Desarrollo', 1 => '1-Produccion'];
    public $newFSede = [
        "name" => '',
        "telefono" => '',
        "direccion" => '',
        "departamento" => '',
        "provincia" => '',
        "distrito" => '',
        "ubigueo" => '',
        "addresstypecode" => '',
        "empresa_id" => '',
    ];

    public function setUp(): array
    {
        $this->user = auth()->user();
        $this->empleado = $this->user->empleados()->first();
        $this->empresas = Empresa::all();

        return [
            PowerGrid::header()
                ->showSearchInput()
                ->includeViewOnTop('components.create-fsede-form'),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount()
                ->pageName('fSedePage'),
        ];
    }

    public function datasource(): Builder
    {
        return FSede::query()->with('empresa');
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('telefono')
            ->add('direccion')
            ->add('departamento')
            ->add('provincia')
            ->add('distrito')
            ->add('ubigueo')
            ->add('addresstypecode')
            ->add('empresa_id', function ($row) {
                if ($this->user->can('edit sede')) {
                    return $this->selectComponent('empresa_id', $row->empresa_id, $row->empresa_id, $this->empresas->pluck('razon_social', 'id'));
                }
                return $row->empresa->razon_social;
            })
            ->add('created_at')
            ->add('created_at_formatted', function ($row) {
                return Carbon::parse($row->created_at)->format('d/m/Y H:i'); //20/01/2024 10:05
            });
    }

    private function selectComponent($field, $empresaId, $selected, $options)
    {
        return Blade::render(
            '<select wire:change="updateField(\'' . $field . '\', $event.target.value, ' . $empresaId . ')">'
                . '<option value="">Seleccionar</option>'
                . '@foreach($options as $value => $label)'
                . '<option value="{{ $value }}" {{ $value == $selected ? \'selected\' : \'\' }}>'
                . '{{ $label }}'
                . '</option>'
                . '@endforeach'
                . '</select>',
            ['options' => $options, 'selected' => $selected]
        );
    }

    public function updateField($field, $value, $empresaId)
    {
        $this->onUpdatedEditable($empresaId, $field, $value);
        $this->dispatch('pg:eventRefresh-default');
    }

    public function onUpdatedEditable(string|int $id, string $field, string $value): void
    {
        Empresa::query()->find($id)->update([
            $field => e($value),
        ]);
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Name', 'name')
                ->sortable()
                ->searchable()
                ->editOnClick(),

            Column::make('Telefono', 'telefono')
                ->sortable()
                ->searchable()
                ->editOnClick(),

            Column::make('Direccion', 'direccion')
                ->sortable()
                ->searchable()
                ->editOnClick(),

            Column::make('Departamento', 'departamento')
                ->sortable()
                ->searchable()
                ->editOnClick(),

            Column::make('Provincia', 'provincia')
                ->sortable()
                ->searchable()
                ->editOnClick(),

            Column::make('Distrito', 'distrito')
                ->sortable()
                ->searchable()
                ->editOnClick(),

            Column::make('Ubigueo', 'ubigueo')
                ->sortable()
                ->searchable()
                ->editOnClick(),

            Column::make('Addresstypecode', 'addresstypecode')
                ->sortable()
                ->searchable()
                ->editOnClick(),

            Column::make('Empresa id', 'empresa_id'),
            Column::make('Created at', 'created_at', 'created_at_formatted')
                ->sortable(),

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
    }

    public function actions(FSede $row): array
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

    public function openCreateForm()
    {
        $this->showCreateForm = true;
    }

    public function closeCreateForm()
    {
        $this->showCreateForm = false;
        $this->reset('newFSede');
    }

    public function createEmpresa()
    {
        $messages = [
            'newFSede.name.required' => '* Obligatorio',
            'newFSede.telefono.required' => '* Obligatorio',
            'newFSede.direccion.required' => '* Obligatorio',
            'newFSede.departamento.required' => '* Obligatorio',
            'newFSede.provincia.required' => '* Obligatorio',
            'newFSede.distrito.required' => '* Obligatorio',
            'newFSede.ubigueo.required' => '* Obligatorio',
            'newFSede.addresstypecode.required' => '* Obligatorio',
            'newFSede.empresa_id.required' => '* Obligatorio',
            'newCliente.empresa_id.exists' => 'Seleccion no es válida',
        ];

        $this->validate([
            'newFSede.name' => 'required',
            'newFSede.telefono' => 'required',
            'newFSede.direccion' => 'required',
            'newFSede.departamento' => 'required',
            'newFSede.provincia' => 'required',
            'newFSede.distrito' => 'required',
            'newFSede.ubigueo' => 'required',
            'newFSede.addresstypecode' => 'required',
            'newFSede.empresa_id' => 'required|exists:empresas,id',
        ], $messages);

        FSede::create($this->newFSede);

        $this->reset('newFSede');
        $this->dispatch('pg:eventRefresh-default');
        $this->dispatch('SweetAlert2', 'Sede creada exitosamente');
    }

    #[On('refresh-sede-table')]
    public function refreshTable(): void
    {
        $this->dispatch('pg:eventRefresh-default');
        $this->setUp();
    }
}
