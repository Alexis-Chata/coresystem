<?php

namespace App\Livewire;

use App\Models\Proveedor;
use App\Models\Empresa;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use Illuminate\Support\Facades\Blade;
use Livewire\Attributes\On;

final class ProveedorTable extends PowerGridComponent
{
    public string $tableName = 'proveedor-table-2bezpg-table';
    public bool $showCreateForm = false;

    public $newProveedor = [
        'codigo' => '',
        'name' => '',
        'empresa_id' => '',
    ];

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput()
                ->includeViewOnTop('components.create-proveedor-form'),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Proveedor::query()
            ->join('empresas', 'proveedors.empresa_id', '=', 'empresas.id')
            ->select('proveedors.*', 'empresas.razon_social as empresa_nombre');
    }

    public function relationSearch(): array
    {
        return [
            'empresa' => ['razon_social'],
        ];
    }

    public function fields(): PowerGridFields
    {
        $empresaOptions = $this->empresaSelectOptions();

        return PowerGrid::fields()
            ->add('id')
            ->add('codigo')
            ->add('name')
            ->add('empresa_id', function ($proveedor) use ($empresaOptions) {
                return $this->selectComponent('empresa_id', $proveedor->id, $proveedor->empresa_id, $empresaOptions);
            })
            ->add('created_at_formatted', fn (Proveedor $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'));
    }

    private function selectComponent($field, $proveedorId, $selected, $options)
    {
        return Blade::render(
            '<select wire:change="updateField(\''. $field .'\', $event.target.value, '. $proveedorId .')">'
            . '@foreach($options as $value => $label)'
            . '<option value="{{ $value }}" {{ $value == $selected ? \'selected\' : \'\' }}>'
            . '{{ $label }}'
            . '</option>'
            . '@endforeach'
            . '</select>',
            ['options' => $options, 'selected' => $selected]
        );
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Código', 'codigo')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Nombre', 'name')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Empresa', 'empresa_id')
                ->sortable(),
            Column::action('Acción')
        ];
    }

    public function filters(): array
    {
        return [
            Filter::datetimepicker('created_at'),
        ];
    }

    public function actions(Proveedor $row): array
    {
        return [
            Button::add('delete')
                ->slot('Eliminar')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('deleteProveedor', ['proveedorId' => $row->id])
        ];
    }

    #[On('deleteProveedor')]
    public function deleteProveedor($proveedorId): void
    {
        Proveedor::destroy($proveedorId);
    }

    public function onUpdatedEditable(string|int $id, string $field, string $value): void
    {
        Proveedor::query()->find($id)->update([
            $field => $value,
        ]);
    }

    public function openCreateForm()
    {
        $this->showCreateForm = true;
    }

    public function closeCreateForm()
    {
        $this->showCreateForm = false;
        $this->reset('newProveedor');
    }

    public function createProveedor()
    {
        $this->validate([
            'newProveedor.codigo' => 'required',
            'newProveedor.name' => 'required',
            'newProveedor.empresa_id' => 'required|exists:empresas,id',
        ]);

        Proveedor::create($this->newProveedor);

        $this->reset('newProveedor');
        $this->dispatch('pg:eventRefresh-default');
        $this->dispatch('proveedor-created', 'Proveedor creado exitosamente');
    }

    public function empresaSelectOptions()
    {
        return Empresa::all()->pluck('razon_social', 'id');
    }

    #[On('updateField')]
    public function updateField($field, $value, $proveedorId)
    {
        $proveedor = Proveedor::find($proveedorId);
        if ($proveedor) {
            $proveedor->update([$field => $value]);
            $this->dispatch('pg:eventRefresh-default');
        }
    }
}