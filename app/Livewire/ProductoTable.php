<?php

namespace App\Livewire;

use App\Models\Producto;
use App\Models\Empresa;
use App\Models\Marca;
use App\Models\Categoria;
use App\Models\FTipoAfectacion;
use App\Models\ProductoComponent;
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
use Illuminate\Support\Facades\DB;

final class ProductoTable extends PowerGridComponent
{
    public string $tableName = 'producto-table-96qpy8-table';
    public bool $showCreateForm = false;
    public string $sortField = 'id';
    public string $sortDirection = 'desc';

    public $newProducto = [
        'name' => '',
        'empresa_id' => '',
        'marca_id' => '',
        'categoria_id' => '',
        'f_tipo_afectacion_id' => '',
        'porcentaje_igv' => '',
        'cantidad' => '',
        'sub_cantidad' => '',
        'tipo' => 'estandar',
        'tipo_unidad' => 'NIU',
        'cantidad_total' => '',
        'components' => []
    ];

    public $editingProductoId = null;

    public function setUp(): array
    {
        //$this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput()
                ->includeViewOnTop('components.create-producto-form')
                ->showSoftDeletes(showMessage: true),
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
            ->select(
                'productos.*',
                'empresas.razon_social as empresa_nombre',
                'marcas.name as marca_nombre',
                'categorias.nombre as categoria_nombre',  // Cambiado de 'name' a 'nombre'
                'f_tipo_afectacions.name as tipo_afectacion_nombre'
            );
    }

    public function relationSearch(): array
    {
        return [
            'empresa' => ['razon_social'],
            'marca' => ['name'],
            'categoria' => ['nombre'],
            'tipoAfectacion' => ['name'],
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
            ->add('name')
            ->add('peso')
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
            ->add('created_at_formatted', fn(Producto $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'));
    }

    private function selectComponent($field, $productoId, $selected, $options)
    {
        return Blade::render(
            '<select wire:change="onUpdatedEditable(' . $productoId . ', \'' . $field . '\', $event.target.value)">'
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
                ->sortable()
                ->searchable(),
            Column::make('Nombre', 'name')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            //Column::make('Empresa', 'empresa_id'),
            Column::make('Marca', 'marca_id'),
            Column::make('Categoría', 'categoria_id'),
            Column::make('Tipo de afectación', 'f_tipo_afectacion_id'),
            Column::make('Porcentaje IGV', 'porcentaje_igv')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Cantidad', 'cantidad')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Sub Cantidad', 'sub_cantidad')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Peso', 'peso')
                ->editOnClick(),
            Column::make('Tipo', 'tipo')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Tipo Unidad', 'tipo_unidad')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::action('Acción')
        ];
    }

    public function onUpdatedEditable(string|int $id, string $field, string $value): void
    {
        Producto::query()->withTrashed()->find($id)->update([
            $field => $value,
        ]);
        $this->dispatch('pg:eventRefresh-producto-lista-precio-table');
    }

    public function actions(Producto $row): array
    {
        $actions = [];

        if ($row->tipo === 'compuesto') {
            $actions[] = Button::add('edit-components')
                ->slot('Editar Componentes')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('editProductoComponents', ['productoId' => $row->id]);
        }

        if ($row->deleted_at) {
            $actions[] = Button::add('restore')
                ->slot('Restaurar')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('restoreProducto', ['productoId' => $row->id]);
        } else {
            $actions[] = Button::add('delete')
                ->slot('Eliminar')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('deleteProducto', ['productoId' => $row->id]);
        }

        return $actions;
    }

    #[On('deleteProducto')]
    public function deleteProducto($productoId): void
    {
        $producto = Producto::withTrashed()->find($productoId);
        if ($producto) {
            $producto->delete();
            $this->dispatch('pg:eventRefresh-producto-lista-precio-table');
            $this->dispatch('producto-deleted', 'Producto eliminado exitosamente');
        }
    }

    #[On('restoreProducto')]
    public function restoreProducto($productoId): void
    {
        $producto = Producto::withTrashed()->find($productoId);
        if ($producto) {
            $producto->restore();
            $this->dispatch('pg:eventRefresh-producto-lista-precio-table');
            $this->dispatch('producto-restored', 'Producto restaurado exitosamente');
        }
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

    public function updateComponents($components)
    {
        $this->newProducto['components'] = collect($components)
            ->filter(function ($component) {
                return !empty($component['producto_id']);
            })
            ->map(function ($component) {
                return [
                    'producto_id' => $component['producto_id'],
                    'cantidad' => $component['cantidad'] ?? 0,
                    'subcantidad' => $component['subcantidad'] ?? 0,
                    'stock' => $component['stock'] ?? 0,
                ];
            })->values()->toArray();
    }

    public function getProductoStock($productoId, $componentId)
    {
        try {
            $producto = Producto::withTrashed()->findOrFail($productoId);
            $stock = $producto->cantidad ?? 0;

            // Agregar un log para depuración
            \Log::info('Stock obtenido:', [
                'productoId' => $productoId,
                'componentId' => $componentId,
                'stock' => $stock
            ]);

            $this->dispatch('update-component-stock', [
                'componentId' => $componentId,
                'stock' => $stock
            ]);

            return $stock; // Retornamos el stock para confirmación

        } catch (\Exception $e) {
            \Log::error('Error al obtener stock:', [
                'error' => $e->getMessage(),
                'productoId' => $productoId,
                'componentId' => $componentId
            ]);
            session()->flash('error', 'Error al obtener el stock: ' . $e->getMessage());
        }
    }

    public function createProducto()
    {
        try {
            DB::beginTransaction();

            // Validación básica para todos los productos
            $baseValidation = [
                'newProducto.name' => 'required|string|max:255',
                'newProducto.empresa_id' => 'required|exists:empresas,id',
                'newProducto.marca_id' => 'required|exists:marcas,id',
                'newProducto.categoria_id' => 'required|exists:categorias,id',
                'newProducto.f_tipo_afectacion_id' => 'required|exists:f_tipo_afectacions,id',
                'newProducto.porcentaje_igv' => 'required|numeric',
                'newProducto.tipo' => 'required|in:estandar,compuesto',
                'newProducto.tipo_unidad' => 'required|string',
            ];

            // Validación específica según el tipo de producto
            if ($this->newProducto['tipo'] === 'estandar') {
                $baseValidation['newProducto.cantidad'] = 'required|numeric|min:1';
                $baseValidation['newProducto.sub_cantidad'] = 'nullable|numeric|min:0';
            } else {
                $baseValidation['newProducto.cantidad_total'] = 'required|numeric|min:1';
                $baseValidation['newProducto.components'] = 'required|array|min:1';
                $baseValidation['newProducto.components.*.producto_id'] = 'required|exists:productos,id';
                $baseValidation['newProducto.components.*.cantidad'] = 'required|numeric|min:1';
                $baseValidation['newProducto.components.*.subcantidad'] = 'nullable|numeric|min:0';
            }

            $this->validate($baseValidation);

            $productoData = [
                'name' => strtoupper($this->newProducto['name']),
                'empresa_id' => $this->newProducto['empresa_id'],
                'marca_id' => $this->newProducto['marca_id'],
                'categoria_id' => $this->newProducto['categoria_id'],
                'f_tipo_afectacion_id' => $this->newProducto['f_tipo_afectacion_id'],
                'porcentaje_igv' => $this->newProducto['porcentaje_igv'],
                'tipo' => $this->newProducto['tipo'],
                'tipo_unidad' => $this->newProducto['tipo_unidad'],
                'cantidad' => $this->newProducto['tipo'] === 'estandar' ? $this->newProducto['cantidad'] : 1,
                'sub_cantidad' => $this->newProducto['tipo'] === 'estandar' ? $this->newProducto['sub_cantidad'] : 0,
                'cantidad_total' => $this->newProducto['cantidad_total'],
            ];

            $producto = Producto::create($productoData);

            if ($this->newProducto['tipo'] === 'compuesto' && !empty($this->newProducto['components'])) {
                foreach ($this->newProducto['components'] as $component) {
                    ProductoComponent::create([
                        'producto_id' => $producto->id,
                        'component_id' => $component['producto_id'],
                        'cantidad' => $component['cantidad'],
                        'subcantidad' => $component['subcantidad'] ?? 0,
                        'cantidad_total' => $this->newProducto['cantidad_total']
                    ]);
                }
            }

            DB::commit();

            $this->dispatch('producto-created', 'Producto creado exitosamente');
            $this->reset('newProducto');
            $this->dispatch('pg:eventRefresh-producto-lista-precio-table');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al crear el producto: ' . $e->getMessage());
        }
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
        return FTipoAfectacion::all()->pluck('name', 'id');
    }

    #[On('editProductoComponents')]
    public function editProductoComponents($productoId): void
    {
        $this->editingProductoId = $productoId;
        $producto = Producto::withTrashed()->with(['componentProducts'])->find($productoId);
        if ($producto) {
            $cantidadTotal = ProductoComponent::where('producto_id', $productoId)
                ->first()
                ->cantidad_total ?? '';

            $components = $producto->componentProducts->map(function ($component) use ($cantidadTotal) {
                $componentProduct = Producto::withTrashed()->find($component->id);

                return [
                    'id' => $component->pivot->id ?? uniqid(),
                    'producto_id' => $component->id,
                    'cantidad' => $component->pivot->cantidad,
                    'subcantidad' => $component->pivot->subcantidad,
                    'stock' => $componentProduct->cantidad ?? 0,
                    'cantidad_total' => $cantidadTotal,
                    'name' => $componentProduct->name
                ];
            })->toArray();

            $this->dispatch('show-edit-components', [
                'components' => $components,
                'cantidadTotal' => $cantidadTotal
            ]);
        }
    }

    public function updateEditingComponents($data)
    {
        try {
            DB::beginTransaction();

            if (!$this->editingProductoId) {
                throw new \Exception('No se ha seleccionado ningún producto para editar');
            }

            $components = $data['components'];
            $cantidadTotal = $data['cantidadTotal'];

            // Validar que haya al menos 2 componentes
            if (count($components) < 1) {
                throw new \Exception('Debe haber al menos 2 componentes');
            }

            // Validar cantidad total
            if (empty($cantidadTotal) || $cantidadTotal < 1) {
                throw new \Exception('La cantidad total debe ser mayor a 0');
            }

            // Validar componentes
            foreach ($components as $component) {
                if (
                    empty($component['producto_id']) ||
                    empty($component['cantidad']) ||
                    $component['cantidad'] > $component['stock']
                ) {
                    throw new \Exception('Datos de componentes inválidos');
                }
            }

            // Eliminar componentes existentes
            ProductoComponent::where('producto_id', $this->editingProductoId)->delete();

            // Crear nuevos componentes
            foreach ($components as $component) {
                ProductoComponent::create([
                    'producto_id' => $this->editingProductoId,
                    'component_id' => $component['producto_id'],
                    'cantidad' => $component['cantidad'],
                    'subcantidad' => $component['subcantidad'] ?? 0,
                    'cantidad_total' => $cantidadTotal
                ]);
            }

            DB::commit();

            // Actualizar la interfaz
            $this->dispatch('components-updated');
            $this->dispatch('pg:eventRefresh-producto-lista-precio-table');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al actualizar los componentes: ' . $e->getMessage());
            $this->dispatch('error', ['message' => $e->getMessage()]);
        }
    }
}
