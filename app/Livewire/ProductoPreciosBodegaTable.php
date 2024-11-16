<?php

namespace App\Livewire;

use App\Models\Producto;
use App\Models\ProductoListaPrecio;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class ProductoPreciosBodegaTable extends PowerGridComponent
{
    public string $tableName = 'producto-precios-bodega-table';

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
            ->select('productos.*', 'marcas.name as marca_nombre')
            ->addSelect([
                'precio_bodega' => ProductoListaPrecio::select('precio')
                    ->whereColumn('producto_id', 'productos.id')
                    ->where('lista_precio_id', 1)
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
            ->add('precio_bodega', fn($model) => $model->precio_bodega ? number_format($model->precio_bodega, 2) : '0.00')
            ->add('precio_unidad', function($model) {
                if ($model->precio_bodega && $model->cantidad > 0) {
                    return number_format($model->precio_bodega / $model->cantidad, 2);
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
            Column::make('Precio Cajón', 'precio_bodega'),
            Column::make('Precio Unidad', 'precio_unidad'),
        ];
    }
}