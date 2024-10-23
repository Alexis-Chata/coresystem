<?php

namespace App\Livewire;

use App\Models\Producto;
use App\Models\Empresa;
use App\Models\Marca;
use App\Models\Categoria;
use App\Models\F_tipo_afectacion;
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

final class ProductoTable extends PowerGridComponent
{
    public string $tableName = 'producto-table-96qpy8-table';
    public bool $showCreateForm = false;

    public $newProducto = [
        'empresa_id' => '',
        'marca_id' => '',
        'categoria_id' => '',
        'f_tipo_afectacion_id' => '',
        'porcentaje_igv' => '',
    ];

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput()
                ->includeViewOnTop('components.create-producto-form'),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
{
    return Producto::query()
        ->join('empresas', 'productos.empresa_id', '=', 'empresas.id')
        ->join('marcas', 'productos.marca_id', '=', 'marcas.id')
        ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
        ->join('f_tipo_afectacions', 'productos.f_tipo_afectacion_id', '=', 'f_tipo_afectacions.id')
        ->select('productos.*', 
                 'empresas.razon_social as empresa_nombre',
                 'marcas.name as marca_nombre',
                 'categorias.nombre as categoria_nombre',  // Cambiado de 'name' a 'nombre'
                 'f_tipo_afectacions.name as tipo_afectacion_nombre');
}

    public function relationSearch(): array
    {
        return [
            'empresa' => ['razon_social'],
            'marca' => ['name'],
            'categoria' => ['name'],
            'tipoAfectacion' => ['tipo_afectacion'],
        ];
    }

    public function fields(): PowerGridFields
    {
        $empresaOptions = $this->empresaSelectOptions();
        $marcaOptions = $this->marcaSelectOptions();
        $categoriaOptions = $this->categoriaSelectOptions();
        $tipoAfectacionOptions = $this->tipoAfectacionSelectOptions();

        return PowerGrid::fields()
            ->add('id')
            ->add('empresa_id', function ($producto) use ($empresaOptions) {
                return $this->selectComponent('empresa_id', $producto->id, $producto->empresa_id, $empresaOptions);
            })
            ->add('marca_id', function ($producto) use ($marcaOptions) {
                return $this->selectComponent('marca_id', $producto->id, $producto->marca_id, $marcaOptions);
            })
            ->add('categoria_id', function ($producto) use ($categoriaOptions) {
                return $this->selectComponent('categoria_id', $producto->id, $producto->categoria_id, $categoriaOptions);
            })
            ->add('f_tipo_afectacion_id', function ($producto) use ($tipoAfectacionOptions) {
                return $this->selectComponent('f_tipo_afectacion_id', $producto->id, $producto->f_tipo_afectacion_id, $tipoAfectacionOptions);
            })
            ->add('porcentaje_igv')
            ->add('created_at_formatted', fn (Producto $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'));
    }

    private function selectComponent($field, $productoId, $selected, $options)
    {
        return Blade::render(
            '<select wire:change="updateField(\''. $field .'\', $event.target.value, '. $productoId .')">'
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
            Column::make('Empresa', 'empresa_id'),
            Column::make('Marca', 'marca_id'),
            Column::make('Categoría', 'categoria_id'),
            Column::make('Tipo de afectación', 'f_tipo_afectacion_id'),
            Column::make('Porcentaje IGV', 'porcentaje_igv')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::action('Acción')
        ];
    }

    public function onUpdatedEditable(string|int $id, string $field, string $value): void
    {
        Producto::query()->find($id)->update([
            $field => $value,
        ]);
    }

    public function actions(Producto $row): array
    {
        return [
            Button::add('delete')
                ->slot('Eliminar')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('deleteProducto', ['productoId' => $row->id])
        ];
    }

    #[On('deleteProducto')]
    public function deleteProducto($productoId): void
    {
        Producto::destroy($productoId);
    }

    public function openCreateForm()
    {
        $this->showCreateForm = true;
    }

    public function closeCreateForm()
    {
        $this->showCreateForm = false;
        $this->reset('newProducto');
    }

    public function createProducto()
    {
        $this->validate([
            'newProducto.empresa_id' => 'required|exists:empresas,id',
            'newProducto.marca_id' => 'required|exists:marcas,id',
            'newProducto.categoria_id' => 'required|exists:categorias,id',
            'newProducto.f_tipo_afectacion_id' => 'required|exists:f_tipo_afectacions,id',
            'newProducto.porcentaje_igv' => 'required|numeric',
        ]);

        Producto::create($this->newProducto);

        $this->reset('newProducto');
        $this->dispatch('pg:eventRefresh-default');
        $this->dispatch('producto-created', 'Producto creado exitosamente');
    }

    public function empresaSelectOptions()
    {
        return Empresa::all()->pluck('razon_social', 'id');
    }

    public function marcaSelectOptions()
    {
        return Marca::all()->pluck('name', 'id');
    }

    public function categoriaSelectOptions()
    {
        return Categoria::all()->pluck('nombre', 'id');
    }

    public function tipoAfectacionSelectOptions()
    {
        return F_tipo_afectacion::all()->pluck('name', 'id');
    }

    #[On('updateField')]
    public function updateField($field, $value, $productoId)
    {
        $producto = Producto::find($productoId);
        if ($producto) {
            $producto->update([$field => $value]);
            $this->dispatch('pg:eventRefresh-default');
        }
    }
}