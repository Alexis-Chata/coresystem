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
    public string $tableName = 'producto-lista-precio-table';
    public bool $showCreateForm = false;
    public string $sortField = 'id';
    public string $sortDirection = 'desc';
    public $precios = [];
    
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
        return Producto::query()
            ->join('marcas', 'productos.marca_id', '=', 'marcas.id')
            ->select([
                'productos.*',
                'marcas.name as marca_nombre'
            ])
            ->when(true, function ($query) {
                $listaPrecios = ListaPrecio::all();
                foreach ($listaPrecios as $listaPrecio) {
                    $query->addSelect([
                        'precio_' . $listaPrecio->id => ProductoListaPrecio::select('precio')
                            ->whereColumn('producto_id', 'productos.id')
                            ->where('lista_precio_id', $listaPrecio->id)
                            ->limit(1)
                    ]);
                }
            });
    }

    public function fields(): PowerGridFields
    {
        $fields = PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('marca_nombre')
            ->add('cantidad');

        $listaPrecios = ListaPrecio::all();
        foreach ($listaPrecios as $listaPrecio) {
            $fields->add('precio_' . $listaPrecio->id, 
                fn($model) => $model->{'precio_' . $listaPrecio->id} 
                    ? number_format($model->{'precio_' . $listaPrecio->id}, 2) 
                    : '-'
            );
        }

        return $fields;
    }

    public function columns(): array
    {
        $columns = [
            Column::make('ID', 'id')
                ->sortable()
                ->searchable(),
            Column::make('Producto', 'name')
                ->sortable()
                ->searchable(),
            Column::make('Marca', 'marca_nombre', 'marcas.name')
                ->sortable()
                ->searchable(),
            Column::make('Cantidad', 'cantidad')
                ->sortable(),
        ];

        $listaPrecios = ListaPrecio::all();
        foreach ($listaPrecios as $listaPrecio) {
            $columns[] = Column::make($listaPrecio->name, 'precio_' . $listaPrecio->id)
                ->sortable()
                ->editOnClick();
        }

        return $columns;
    }

    public function createProductoListaPrecio()
    {
        $this->validate([
            'newProductoListaPrecio.producto_id' => 'required|exists:productos,id',
            'precios.*' => 'required|numeric|min:0',
        ]);

        $producto_id = $this->newProductoListaPrecio['producto_id'];

        foreach ($this->precios as $lista_precio_id => $precio) {
            ProductoListaPrecio::updateOrCreate(
                [
                    'producto_id' => $producto_id,
                    'lista_precio_id' => $lista_precio_id
                ],
                ['precio' => $precio]
            );
        }

        $this->reset(['newProductoListaPrecio', 'precios']);
        $this->dispatch('pg:eventRefresh-default');
        $this->dispatch('producto-lista-precio-created');
        $this->dispatch('producto-lista-precio-SweetAlert2', 'Precios actualizados exitosamente');
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
        if (str_starts_with($field, 'precio_')) {
            $listaPrecioId = substr($field, 7); // Extraer el ID de la lista de precio
            
            $productoListaPrecio = ProductoListaPrecio::updateOrCreate(
                [
                    'producto_id' => $id,
                    'lista_precio_id' => $listaPrecioId
                ],
                ['precio' => $value]
            );
            
            $this->dispatch('pg:eventRefresh-default');
            $this->dispatch('producto-lista-precio-SweetAlert2', 'Precio actualizado exitosamente');
        }
    }
}