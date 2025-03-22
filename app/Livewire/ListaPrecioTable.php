<?php

namespace App\Livewire;

use App\Models\ListaPrecio;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Facades\Rule;
use App\Models\Empresa;

final class ListaPrecioTable extends PowerGridComponent
{
    public string $tableName = 'lista-precio-table-zdubif-table';
    public bool $showCreateForm = false;

    public $newListaPrecio = [
        'name' => '',
        'descripcion' => '',
        'empresa_id' => '',
    ];

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->showSoftDeletes()
                ->showSearchInput()
                ->includeViewOnTop('components.create-lista-precio-form'),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return ListaPrecio::query()->with(['empresa']);
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('descripcion')
            ->add('empresa', fn($listaPrecio) => e($listaPrecio->empresa->razon_social));
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Nombre', 'name')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Descripción', 'descripcion')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::action('Action')
        ];
    }

    public function onUpdatedEditable(string|int $id, string $field, string $value): void
    {
        ListaPrecio::find($id)->update([$field => $value]);
    }

    #[\Livewire\Attributes\On('restoreListaPrecio')]
    public function restore($rowId): void
    {
        $listaPrecio = ListaPrecio::withTrashed()->find($rowId);
        if ($listaPrecio) {
            $listaPrecio->restore();

            $this->dispatch('pg:eventRefresh-default');
            $this->dispatch('lista-precio-SweetAlert2', 'Restaurado exitosamente');
        }
    }

    #[\Livewire\Attributes\On('deleteListaPrecio')]
    public function delete($rowId): void
    {
        $listaPrecio = ListaPrecio::find($rowId);
        if ($listaPrecio) {
            $listaPrecio->delete();

            $this->dispatch('pg:eventRefresh-default');
            $this->dispatch('lista-precio-SweetAlert2', 'Eliminado exitosamente');
        }
    }

    public function actions(ListaPrecio $row): array
    {
        $actions = [];

        $actions[] = Button::add('restore')
            ->slot('Restaurar')
            ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
            ->dispatch('restoreListaPrecio', ['rowId' => $row->id]);

        $actions[] = Button::add('delete')
            ->slot('Eliminar')
            ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
            ->dispatch('deleteListaPrecio', ['rowId' => $row->id]);

        return $actions;
    }

    public function actionRules($row): array
    {
        return [
            // Ocultar botón restaurar si el registro no está eliminado
            Rule::button('restore')
                ->when(fn($row) => !$row->trashed())
                ->hide(),
            // Ocultar botón eliminar si el registro está eliminado
            Rule::button('delete')
                ->when(fn($row) => $row->trashed())
                ->hide(),
        ];
    }

    public function createListaPrecio()
    {
        $this->validate([
            'newListaPrecio.name' => 'required|string',
            'newListaPrecio.descripcion' => 'nullable|string',
            'newListaPrecio.empresa_id' => 'required|exists:empresas,id',
        ]);

        ListaPrecio::create($this->newListaPrecio);

        $this->reset('newListaPrecio');
        $this->dispatch('pg:eventRefresh-default');
        $this->dispatch('lista-precio-SweetAlert2', 'Lista de precio creada exitosamente');
    }
}
