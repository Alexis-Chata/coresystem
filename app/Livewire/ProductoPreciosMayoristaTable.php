<?php

namespace App\Livewire;

use App\Models\Producto;
use App\Models\ProductoListaPrecio;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class ProductoPreciosMayoristaTable extends PowerGridComponent
{
    public string $tableName = 'producto-precios-mayorista-table';

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
        return Producto::query()
            ->join('marcas', 'productos.marca_id', '=', 'marcas.id')
            ->select([
                'productos.*',
                'marcas.name as marca_nombre'
            ])
            ->addSelect([
                'precio_mayorista' => ProductoListaPrecio::select('precio')
                    ->whereColumn('producto_id', 'productos.id')
                    ->where('lista_precio_id', 2)
                    ->limit(1)
            ]);
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('marca_nombre')
            ->add('cantidad')
            ->add('precio_mayorista', fn($model) => $model->precio_mayorista ? number_format($model->precio_mayorista, 2) : '0.00')
            ->add('precio_unidad', function($model) {
                if ($model->precio_mayorista && $model->cantidad > 0) {
                    return number_format($model->precio_mayorista / $model->cantidad, 2);
                }
                return '0.00';
            });
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->sortable(),
            Column::make('Producto', 'name')
                ->sortable()
                ->searchable(),
            Column::make('Marca', 'marca_nombre', 'marcas.name')
                ->sortable()
                ->searchable(),
            Column::make('Cantidad', 'cantidad')
                ->sortable(),
            Column::make('Precio Cajón', 'precio_mayorista'),
            Column::make('Precio Unidad', 'precio_unidad'),
        ];
    }
    
}