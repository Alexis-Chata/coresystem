<?php

namespace App\Livewire;

use App\Models\Marca;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class MarcaTable extends PowerGridComponent
{
    public string $tableName = 'marca-table-kwk1j9-table';

    public function setUp(): array
{
    $this->showCheckBox();

    return [
        PowerGrid::header()
            ->showSearchInput()
            ->includeViewOnTop('components.create-marca-form'),
        PowerGrid::footer()
            ->showPerPage()
            ->showRecordCount(),
    ];
}
public bool $showCreateForm = false;

public $newMarca = [
    'codigo' => '',
    'name' => '',
    'empresa_id' => '',
];

public function openCreateForm()
{
    $this->showCreateForm = true;
}

public function closeCreateForm()
{
    $this->showCreateForm = false;
    $this->reset('newMarca');
}

public function createMarca()
{
    $this->validate([
        'newMarca.codigo' => 'required|unique:marcas,codigo',
        'newMarca.name' => 'required',
        'newMarca.empresa_id' => 'required|exists:empresas,id',
    ]);

    $marca = Marca::create($this->newMarca);
    $marca->nro_orden = $marca->id;
    $marca->save();

    $this->reset('newMarca');
    $this->dispatch('pg:eventRefresh-default');
    $this->dispatch('marca-created', 'Marca creada exitosamente');
}
    public function datasource(): Builder
    {
        return Marca::query();
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
            ->add('empresa_id')
            ->add('created_at');
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Codigo', 'codigo')
                ->sortable()
                ->searchable()
                ->editOnClick(),

            Column::make('Name', 'name')
                ->sortable()
                ->searchable()
                ->editOnClick(),

            Column::make('Empresa id', 'empresa_id')
                ->sortable()
                ->searchable()
                ->editOnClick(),

            Column::action('Action')
        ];
    }


    // Funcion para actualizar los campos editables
    public function onUpdatedEditable(string|int $id, string $field, string $value): void
    {
        Marca::query()->find($id)->update([
            $field => $value,
        ]);
    }
    // Fin de la funcion para actualizar los campos editables



    public function actions(Marca $row): array
    {
        return [
            // Funcion para eliminar una marca, se debe crear una funcion en el componente hijo para eliminar la marca.
            Button::add('delete')
                ->slot('Eliminar')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('deleteMarca', ['marcaId' => $row->id])
            // Fin de la funcion para eliminar una marca
        ];
    }

    // Componente hijo para eliminar una marca
    #[\Livewire\Attributes\On('deleteMarca')]
    public function deleteMarca($marcaId): void
    {
        Marca::destroy($marcaId);
    }
    // Fin del componente hijo para eliminar una marca

}
