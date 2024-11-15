<?php

namespace App\Livewire;

use App\Models\ProductoListaPrecio;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Facades\Rule;
use App\Models\Producto;
use App\Models\ListaPrecio;
use Illuminate\Support\Facades\Blade;
use Livewire\Attributes\On;

final class ProductoListaPrecioTable extends PowerGridComponent
{
    public string $tableName = 'producto-lista-precio-table-0ikqpf-table';
    public bool $showCreateForm = false;
    
    public $newProductoListaPrecio = [
        'producto_id' => '',
        'lista_precio_id' => '',
        'precio' => '',
    ];

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->showSoftDeletes()
                ->showSearchInput()
                ->includeViewOnTop('components.create-producto-lista-precio-form'),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return ProductoListaPrecio::query()
            ->with(['producto', 'listaPrecio']);
    }

    public function fields(): PowerGridFields
    {
        $productosOptions = $this->productosSelectOptions();
        $listaPreciosOptions = $this->listaPreciosSelectOptions();

        return PowerGrid::fields()
            ->add('id')
            ->add('producto_id', function ($model) use ($productosOptions) {
                return $this->selectComponent('producto_id', $model->id, $model->producto_id, $productosOptions);
            })
            ->add('lista_precio_id', function ($model) use ($listaPreciosOptions) {
                return $this->selectComponent('lista_precio_id', $model->id, $model->lista_precio_id, $listaPreciosOptions);
            })
            ->add('precio', fn($model) => number_format($model->precio, 2))
            ->add('created_at_formatted', fn ($model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'));
    }

    private function selectComponent($field, $modelId, $selected, $options)
    {
        return Blade::render(
            '<select wire:change="updateField(\''. $field .'\', $event.target.value, '. $modelId .')">'
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
            Column::make('Id', 'id')
                ->sortable(),
            Column::make('Producto', 'producto_id')
                ->sortable(),
            Column::make('Lista de Precio', 'lista_precio_id')
                ->sortable(),
            Column::make('Precio', 'precio')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::action('Acción')
        ];
    }

    #[\Livewire\Attributes\On('restoreProductoListaPrecio')]
    public function restore($rowId): void
    {
        $productoListaPrecio = ProductoListaPrecio::withTrashed()->find($rowId);
        if ($productoListaPrecio) {
            $productoListaPrecio->restore();
            
            $this->dispatch('pg:eventRefresh-default');
            $this->dispatch('producto-lista-precio-SweetAlert2', 'Restaurado exitosamente');
        }
    }

    #[\Livewire\Attributes\On('deleteProductoListaPrecio')]
    public function delete($rowId): void
    {
        $productoListaPrecio = ProductoListaPrecio::find($rowId);
        if ($productoListaPrecio) {
            $productoListaPrecio->delete();
            
            $this->dispatch('pg:eventRefresh-default');
            $this->dispatch('producto-lista-precio-SweetAlert2', 'Eliminado exitosamente');
        }
    }

    public function actions(ProductoListaPrecio $row): array
    {
        $actions = [];

        $actions[] = Button::add('restore')
            ->slot('Restaurar')
            ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
            ->dispatch('restoreProductoListaPrecio', ['rowId' => $row->id]);

        $actions[] = Button::add('delete')
            ->slot('Eliminar')
            ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
            ->dispatch('deleteProductoListaPrecio', ['rowId' => $row->id]);

        return $actions;
    }

    public function actionRules($row): array
    {
        return [
            Rule::button('restore')
                ->when(fn($row) => !$row->trashed())
                ->hide(),
            Rule::button('delete')
                ->when(fn($row) => $row->trashed())
                ->hide(),
        ];
    }

    public function createProductoListaPrecio()
    {
        $this->validate([
            'newProductoListaPrecio.producto_id' => 'required|exists:productos,id',
            'newProductoListaPrecio.lista_precio_id' => 'required|exists:lista_precios,id',
            'newProductoListaPrecio.precio' => 'required|numeric|min:0',
        ]);

        ProductoListaPrecio::create($this->newProductoListaPrecio);

        $this->reset('newProductoListaPrecio');
        $this->dispatch('pg:eventRefresh-default');
        $this->dispatch('producto-lista-precio-SweetAlert2', 'Precio creado exitosamente');
    }

    // Métodos auxiliares para los selects
    private function productosSelectOptions()
    {
        return Producto::all()->pluck('name', 'id');
    }

    private function listaPreciosSelectOptions()
    {
        return ListaPrecio::all()->pluck('name', 'id');
    }

    #[On('updateField')]
    public function updateField($field, $value, $modelId)
    {
        $productoListaPrecio = ProductoListaPrecio::find($modelId);
        if ($productoListaPrecio) {
            $productoListaPrecio->update([$field => $value]);
            $this->dispatch('pg:eventRefresh-default');
            $this->dispatch('producto-lista-precio-SweetAlert2', 'Actualizado exitosamente');
        }
    }

    public function onUpdatedEditable(string|int $id, string $field, string $value): void
{
    $productoListaPrecio = ProductoListaPrecio::query()->find($id);
    if ($productoListaPrecio) {
        $productoListaPrecio->update([
            $field => $value,
        ]);
        
        $this->dispatch('pg:eventRefresh-default');
        $this->dispatch('producto-lista-precio-SweetAlert2', 'Actualizado exitosamente');
    }
}
}