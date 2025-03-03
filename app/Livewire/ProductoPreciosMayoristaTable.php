<?php

namespace App\Livewire;

use App\Exports\PreciosExport;
use App\Models\Producto;
use App\Models\ProductoListaPrecio;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class ProductoPreciosMayoristaTable extends PowerGridComponent
{
    public string $tableName = 'producto-precios-mayorista-table';
    public $lista_precio_id = 2;

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->showSearchInput()
                ->showSoftDeletes(showMessage: true)
                ->includeViewOnTop('components.view-on-top'),
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
                    ->where('lista_precio_id', $this->lista_precio_id)
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
            Column::make('Precio Cajón', 'precio_mayorista'),
            Column::make('Precio Unidad', 'precio_unidad'),
        ];
    }

    public function descargar_lista_precios(){
        $lista_precio_id = $this->lista_precio_id;
        $name = match ($lista_precio_id) {
            1 => 'Lista Bodega.xlsx',
            2 => 'Lista Mayorista.xlsx',
            default => 'Lista de Precios.xlsx',
        };
        return Excel::download(new PreciosExport($lista_precio_id), $name);
    }
}
