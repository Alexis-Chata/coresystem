<?php

namespace App\Livewire;

use App\Models\Ruta;
use App\Models\Empleado;
use App\Models\Empresa;
use App\Models\Lista_precio;
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

final class RutaTable extends PowerGridComponent
{
    public string $tableName = 'ruta-table-anq9ti-table';
    public bool $showCreateForm = false;

    public $newRuta = [
        'codigo' => '',
        'name' => '',
        'vendedor_id' => '',
        'empresa_id' => '',
        'lista_precio_id' => '',
    ];

    public function setUp(): array
    {
        $header = PowerGrid::header()
            ->showSearchInput();

        if (auth()->user()->can('view menuEmpleado')) {
            $header->includeViewOnTop('components.create-ruta-form');
        }

        return [
            $header,
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        $empleado = auth()->user()->empleados()->first();

        $query = Ruta::query()
            ->join('empleados', 'rutas.vendedor_id', '=', 'empleados.id')
            ->join('empresas', 'rutas.empresa_id', '=', 'empresas.id')
            ->join('lista_precios', 'rutas.lista_precio_id', '=', 'lista_precios.id')
            ->select('rutas.*', 
                     'empleados.name as vendedor_nombre',
                     'empresas.razon_social as empresa_nombre',
                     'lista_precios.name as lista_precio_nombre');

        if ($empleado && $empleado->tipo_empleado === 'vendedor') {
            $query->where('rutas.vendedor_id', $empleado->id);
        }

        return $query;
    }

    public function relationSearch(): array
    {
        return [
            'vendedor' => ['name'],
            'empresa' => ['razon_social'],
            'listaPrecio' => ['name'],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('codigo')
            ->add('name')
            ->add('vendedor_id', function ($ruta) {
                return $this->selectComponent('vendedor_id', $ruta->id, $ruta->vendedor_id, $this->vendedorSelectOptions());
            })
            ->add('empresa_id', function ($ruta) {
                return $this->selectComponent('empresa_id', $ruta->id, $ruta->empresa_id, $this->empresaSelectOptions());
            })
            ->add('lista_precio_id', function ($ruta) {
                return $this->selectComponent('lista_precio_id', $ruta->id, $ruta->lista_precio_id, $this->listaPrecioSelectOptions());
            })
            ->add('created_at_formatted', fn (Ruta $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'));
    }

    private function selectComponent($field, $rutaId, $selected, $options)
    {
        return Blade::render(
            '<select wire:change="updateField(\''. $field .'\', $event.target.value, '. $rutaId .')">'
            . '<option value="">Seleccionar</option>'
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
            Column::make('Nombre', 'name')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Vendedor', 'vendedor_id')
                ->sortable(),
            Column::make('Lista de Precios', 'lista_precio_id')
                ->sortable(),
            Column::action('Acción')
                ->visibleInExport(false)
                ->hidden(!auth()->user()->can('view menuEmpleado'))
        ];
    }

    public function filters(): array
    {
        return [
            Filter::datetimepicker('created_at'),
        ];
    }

    public function actions(Ruta $row): array
    {
        if (!auth()->user()->can('view menuEmpleado')) {
            return [];
        }

        return [
            Button::add('delete')
                ->slot('Eliminar')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('deleteRuta', ['rutaId' => $row->id])
        ];
    }

    #[On('deleteRuta')]
    public function deleteRuta($rutaId): void
    {
        Ruta::destroy($rutaId);
    }

    public function onUpdatedEditable(string|int $id, string $field, string $value): void
    {
        Ruta::query()->find($id)->update([
            $field => $value,
        ]);
    }

    public function openCreateForm()
    {
        $this->showCreateForm = true;
    }

    public function closeCreateForm()
    {
        $this->showCreateForm = false;
        $this->reset('newRuta');
    }

    public function createRuta()
    {
        $empleado = auth()->user()->empleados()->first();
        
        if ($empleado && $empleado->tipo_empleado === 'vendedor') {
            $this->newRuta['vendedor_id'] = $empleado->id;
        }

        $this->validate([
            'newRuta.codigo' => 'required',
            'newRuta.name' => 'required',
            'newRuta.vendedor_id' => 'required|exists:empleados,id',
            'newRuta.empresa_id' => 'required|exists:empresas,id',
            'newRuta.lista_precio_id' => 'required|exists:lista_precios,id',
        ]);

        Ruta::create($this->newRuta);

        $this->reset('newRuta');
        $this->dispatch('pg:eventRefresh-default');
        $this->dispatch('ruta-created', 'Ruta creada exitosamente');
    }

    public function vendedorSelectOptions()
    {
        $empleado = auth()->user()->empleados()->first();
        
        if ($empleado && $empleado->tipo_empleado === 'vendedor') {
            return collect([$empleado->id => $empleado->name]);
        }
        
        return Empleado::where('tipo_empleado', 'vendedor')->pluck('name', 'id');
    }

    public function empresaSelectOptions()
    {
        return Empresa::all()->pluck('razon_social', 'id');
    }

    public function listaPrecioSelectOptions()
    {
        return Lista_precio::all()->pluck('name', 'id');
    }

    #[On('updateField')]
    public function updateField($field, $value, $rutaId)
    {
        $ruta = Ruta::find($rutaId);
        if ($ruta) {
            $ruta->update([$field => $value]);
            $this->dispatch('pg:eventRefresh-default');
        }
    }
}