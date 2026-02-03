<?php

namespace App\Livewire;

use App\Exports\ProductosExport;
use App\Models\Producto;
use App\Models\Empresa;
use App\Models\Marca;
use App\Models\Categoria;
use App\Models\FTipoAfectacion;
use App\Models\ListaPrecio;
use App\Models\ProductoComponent;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use Illuminate\Support\Facades\Blade;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

final class ProductoTable extends PowerGridComponent
{
    public string $tableName = 'producto-table-96qpy8-table';
    public bool $showCreateForm = false;
    public string $sortField = 'id';
    public string $sortDirection = 'desc';
    public ?int $lista_precio_id = null;
    public $listasPrecio; // opciones para el select

    public $newProducto = [
        'name' => '',
        'empresa_id' => '1',
        'marca_id' => '',
        'categoria_id' => '1',
        'f_tipo_afectacion_id' => '10',
        'porcentaje_igv' => '18',
        'cantidad' => '',
        'sub_cantidad' => '1',
        'peso' => '1.000', // 👈 AQUÍ
        'tipo' => 'estandar',
        'tipo_unidad' => 'NIU',
        'cantidad_total' => '',
        'components' => []
    ];

    public $editingProductoId = null;

    public function setUp(): array
    {
        $this->listasPrecio = ListaPrecio::orderBy('name')->pluck('name', 'id'); // ajusta campo
        $this->lista_precio_id = $this->lista_precio_id ?? $this->listasPrecio->keys()->first();

        $header = PowerGrid::header()->showSearchInput();

        $header->includeViewOnTop('components.create-producto-form');

        $header->showSoftDeletes(showMessage: true);

        return [
            $header,
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        $lp = $this->lista_precio_id ?? -1; // si no hay lista, no matchea nada

        return Producto::query()
            ->join('empresas', 'productos.empresa_id', '=', 'empresas.id')
            ->join('marcas', 'productos.marca_id', '=', 'marcas.id')
            ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->join('f_tipo_afectacions', 'productos.f_tipo_afectacion_id', '=', 'f_tipo_afectacions.id')

            // pivote por lista seleccionada
            ->leftJoin('producto_lista_precios as plp', function ($join) use ($lp) {
                $join->on('plp.producto_id', '=', 'productos.id')
                    ->where('plp.lista_precio_id', '=', $lp);
            })
            ->select(
                'productos.*',
                'empresas.razon_social as empresa_nombre',
                'marcas.name as marca_nombre',
                'categorias.nombre as categoria_nombre',  // Cambiado de 'name' a 'nombre'
                'f_tipo_afectacions.name as tipo_afectacion_nombre',
                DB::raw('plp.precio as precio_lista'),
                DB::raw('plp.activo as activo_lista')
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
        $canEdit = $this->canEditProducto();

        $empresaOptions = $this->empresaSelectOptions();
        $marcaOptions = $this->marcaSelectOptions();
        $categoriaOptions = $this->categoriaSelectOptions();
        $tipoAfectacionOptions = $this->tipoAfectacionSelectOptions();

        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('peso', fn($producto) => number_format($producto->peso ?? '0', 3, '.', ''))

            ->add('empresa_id', function ($producto) use ($empresaOptions, $canEdit) {
                return $canEdit
                    ? $this->selectComponent('empresa_id', $producto->id, $producto->empresa_id, $empresaOptions)
                    : ($empresaOptions->get($producto->empresa_id) ?? '');
            })

            ->add('marca_id', function ($producto) use ($marcaOptions, $canEdit) {
                return $canEdit
                    ? $this->selectComponent('marca_id', $producto->id, $producto->marca_id, $marcaOptions)
                    : ($marcaOptions->get($producto->marca_id) ?? '');
            })

            ->add('categoria_id', function ($producto) use ($categoriaOptions, $canEdit) {
                return $canEdit
                    ? $this->selectComponent('categoria_id', $producto->id, $producto->categoria_id, $categoriaOptions)
                    : ($categoriaOptions->get($producto->categoria_id) ?? '');
            })

            ->add('f_tipo_afectacion_id', function ($producto) use ($tipoAfectacionOptions, $canEdit) {
                return $canEdit
                    ? $this->selectComponent('f_tipo_afectacion_id', $producto->id, $producto->f_tipo_afectacion_id, $tipoAfectacionOptions)
                    : ($tipoAfectacionOptions->get($producto->f_tipo_afectacion_id) ?? '');
            })

            ->add('porcentaje_igv')
            ->add('created_at_formatted', fn(Producto $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'))

            ->add('tipo', function ($producto) use ($canEdit) {
                $labels = collect([
                    'estandar' => 'Estandar',
                    'compuesto' => 'Compuesto',
                ]);

                return $canEdit
                    ? $this->selectComponent('tipo', $producto->id, $producto->tipo, $labels)
                    : ($labels->get($producto->tipo) ?? '');
            })

            ->add('activo_lista', function ($p) {
                if (is_null($p->activo_lista)) return 'NO ASIGNADO';
                return $p->activo_lista ? 'ACTIVO' : 'INACTIVO';
            });
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
        $canEdit = $this->canEditProducto();

        $colNombre = Column::make('Nombre', 'name')->sortable()->searchable();
        if ($canEdit) $colNombre->editOnClick();

        $colIgv = Column::make('Porcentaje IGV', 'porcentaje_igv')->sortable()->searchable();
        if ($canEdit) $colIgv->editOnClick();

        $colCantidad = Column::make('Cantidad', 'cantidad')->sortable()->searchable();
        if ($canEdit) $colCantidad->editOnClick();

        $colSubCantidad = Column::make('Sub Cantidad', 'sub_cantidad')->sortable()->searchable();
        if ($canEdit) $colSubCantidad->editOnClick();

        $colPeso = Column::make('Peso (Kg)', 'peso');
        if ($canEdit) $colPeso->editOnClick();

        $colTipoUnidad = Column::make('Tipo Unidad', 'tipo_unidad')->sortable()->searchable();
        if ($canEdit) $colTipoUnidad->editOnClick();

        return [
            Column::make('Id', 'id')->sortable()->searchable(),
            $colNombre,
            Column::make('Marca', 'marca_id'),
            Column::make('Categoría', 'categoria_id'),
            Column::make('Tipo de afectación', 'f_tipo_afectacion_id'),
            $colIgv,
            $colCantidad,
            $colSubCantidad,
            $colPeso,
            Column::make('Tipo', 'tipo')->sortable()->searchable(),
            $colTipoUnidad,
            Column::make('Estado Lista', 'activo_lista'),
            Column::action('Acción'),
        ];
    }

    public function onUpdatedEditable(string|int $id, string $field, string $value): void
    {
        if (!$this->canEditProducto()) {
            session()->flash('error', 'No autorizado para editar productos.');
            return;
        }
        $producto = Producto::withTrashed()->find($id);

        if (!$producto) {
            return;
        }

        switch ($field) {

            case 'peso':
                // Validación
                if (!is_numeric($value) || $value < 0) {
                    session()->flash('error', 'El peso debe ser un número mayor o igual a 0');
                    return;
                }

                $update_data = [
                    'peso' => (float) $value
                ];
                break;

            case 'porcentaje_igv':
                if (!is_numeric($value) || $value < 0) {
                    session()->flash('error', 'El IGV debe ser un número válido');
                    return;
                }

                $update_data = [
                    'porcentaje_igv' => (float) $value
                ];
                break;

            case 'cantidad':
            case 'sub_cantidad':
                if (!is_numeric($value) || $value <= 0) {
                    session()->flash('error', 'La cantidad debe ser un número válido');
                    return;
                }

                $update_data = [
                    $field => (float) $value
                ];
                break;

            case 'tipo':
                if (!in_array($value, ['estandar', 'compuesto'])) {
                    session()->flash('error', 'Tipo de producto inválido');
                    return;
                }

                $update_data = [
                    'tipo' => strtolower($value)
                ];
                break;

            default:
                // Campos de texto
                $update_data = [
                    $field => strtoupper($value)
                ];
                break;
        }

        $producto->update($update_data);

        $this->dispatch('pg:eventRefresh-producto-lista-precio-table');
    }

    public function actions(Producto $row): array
    {
        $actions = [];

        // Editar componentes -> solo si puede editar
        if ($row->tipo === 'compuesto' && $this->canEditProducto()) {
            $actions[] = Button::add('edit-components')
                ->slot('Editar Componentes')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('editProductoComponents', ['productoId' => $row->id]);
        }

        // Eliminar / Restaurar -> solo si puede eliminar
        if ($this->canDeleteProducto()) {
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

            if (!is_null($row->activo_lista)) {
                $actions[] = Button::add('toggle-lp')
                    ->slot('Activar/Desactivar Lista')
                    ->id()
                    ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600')
                    ->dispatch('toggleProductoListaPrecio', ['productoId' => $row->id]);
            }
        }

        return $actions;
    }

    #[On('toggleProductoListaPrecio')]
    public function toggleProductoListaPrecio(int $productoId): void
    {
        if (! $this->canDeleteProducto()) {
            session()->flash('error', 'No autorizado.');
            return;
        }

        if (! $this->lista_precio_id) {
            session()->flash('error', 'Seleccione una lista de precio.');
            return;
        }

        $pivot = DB::table('producto_lista_precios')
            ->where('producto_id', $productoId)
            ->where('lista_precio_id', $this->lista_precio_id)
            ->first();

        // ✅ si NO existe pivote: no crear nada
        if (! $pivot) return;

        Producto::withTrashed()->findOrFail($productoId)
            ->listaPrecios()
            ->updateExistingPivot($this->lista_precio_id, [
                'activo' => $pivot->activo ? 0 : 1
            ]);

        $this->dispatch("pg:eventRefresh-{$this->tableName}");
    }


    private function canCreateProducto(): bool
    {
        return auth()->check() && auth()->user()->can('create producto');
    }

    private function canEditProducto(): bool
    {
        return auth()->check() && auth()->user()->can('edit producto');
    }

    private function canDeleteProducto(): bool
    {
        return auth()->check() && auth()->user()->can('delete producto');
    }

    #[On('deleteProducto')]
    public function deleteProducto($productoId): void
    {
        if (!$this->canDeleteProducto()) {
            session()->flash('error', 'No autorizado para eliminar productos.');
            return;
        }

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
        if (!$this->canDeleteProducto()) {
            session()->flash('error', 'No autorizado para restaurar productos.');
            return;
        }

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
            Log::info('Stock obtenido:', [
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
            Log::error('Error al obtener stock:', [
                'error' => $e->getMessage(),
                'productoId' => $productoId,
                'componentId' => $componentId
            ]);
            session()->flash('error', 'Error al obtener el stock: ' . $e->getMessage());
        }
    }

    public function createProducto()
    {
        if (!$this->canCreateProducto()) {
            session()->flash('error', 'No autorizado para crear productos.');
            return;
        }
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
                'newProducto.peso' => 'required|numeric|min:0', // 👈 AQUÍ
                'newProducto.tipo' => 'required|in:estandar,compuesto',
                'newProducto.tipo_unidad' => 'required|string',
            ];

            // Validación específica según el tipo de producto
            if ($this->newProducto['tipo'] === 'estandar') {
                $baseValidation['newProducto.cantidad'] = 'required|numeric|min:1';
                $baseValidation['newProducto.sub_cantidad'] = 'required|numeric|min:1';
            } else {
                $baseValidation['newProducto.cantidad_total'] = 'required|numeric|min:1';
                $baseValidation['newProducto.components'] = 'required|array|min:1';
                $baseValidation['newProducto.components.*.producto_id'] = 'required|exists:productos,id';
                $baseValidation['newProducto.components.*.cantidad'] = 'required|numeric|min:1';
                $baseValidation['newProducto.components.*.subcantidad'] = 'required|numeric|min:1';
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
                'peso' => $this->newProducto['peso'], // 👈 AQUÍ
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

    public function descargar_productos()
    {
        $filename = 'Productos.xlsx';
        return Excel::download(new ProductosExport, $filename);
    }
}
