<?php

namespace App\Livewire;

use App\Models\Ruta;
use App\Models\Empleado;
use App\Models\Empresa;
use App\Models\ListaPrecio;
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
    public string $sortField = 'id';
    public string $sortDirection = 'desc';

    public $newRuta = [
        'codigo' => '',
        'name' => '',
        'vendedor_id' => '',
        'empresa_id' => '',
        'lista_precio_id' => '',
        'dia_visita' => '',
    ];

    public function setUp(): array
    {
        $header = PowerGrid::header()
            ->showSearchInput();

        if (auth()->user()->can('create ruta')) {
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
                // Permiso Autorizado se muestra el select para editar,
                // sino solo se muestra el input text con el nombre del vendedor,
                // lo hacemos desde fields para dar la logica y luego imprimirlo en la columna.
                if (auth()->user()->can('edit ruta')) {
                    return $this->selectComponent('vendedor_id', $ruta->id, $ruta->vendedor_id, $this->vendedorSelectOptions());
                }
                return $ruta->vendedor_nombre;
            })
            ->add('lista_precio_id', function ($ruta) {
                if (auth()->user()->can('edit ruta')) {
                    return $this->selectComponent('lista_precio_id', $ruta->id, $ruta->lista_precio_id, $this->listaPrecioSelectOptions());
                }
                return $ruta->lista_precio_nombre;
            })
            ->add('created_at_formatted', fn (Ruta $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'))
            ->add('dia_visita', function ($ruta) {
                if (auth()->user()->can('edit ruta')) {
                    return $this->selectComponent('dia_visita', $ruta->id, $ruta->dia_visita, $this->diasVisitaOptions());
                }
                return $ruta->dia_visita;
            });
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
        $columns = [
            Column::make('Id', 'id')
                ->sortable()
                ->searchable(),
            Column::make('Nombre', 'name')
                ->sortable()
                ->searchable()
                ->editOnClick(
                    // Documentacion PowerGrid
                    hasPermission: auth()->user()->can('edit ruta')
                ),
            Column::make('Día de visita', 'dia_visita')
                ->sortable()
                ->searchable(),
        ];

        $empleado = auth()->user()->empleados()->first();
        // Solo mostrar la columna de vendedor si NO es un vendedor
        if (!$empleado || $empleado->tipo_empleado !== 'vendedor') {
            $columns[] = Column::make('Vendedor', 'vendedor_id')
                ->sortable();
        }

        $columns[] = Column::make('Lista de Precios', 'lista_precio_id')
            ->sortable()
            ->field('lista_precio_id');

        $columns[] = Column::action('Acción')
            ->visibleInExport(false)
            // Documentacion PowerGrid
            ->hidden(!auth()->user()->can('delete ruta'));

        return $columns;
    }

    public function filters(): array
    {
        return [
            Filter::datetimepicker('created_at'),
        ];
    }

    public function actions(Ruta $row): array
    {
        return [
            Button::add('delete')
                ->slot('Eliminar')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('deleteRuta', ['rutaId' => $row->id])
                // Documentacion PowerGrid
                ->can(auth()->user()->can('delete ruta'))
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
        return ListaPrecio::all()->pluck('name', 'id');
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
    private function diasVisitaOptions()
    {
        return [
            'Lunes' => 'Lunes',
            'Martes' => 'Martes',
            'Miércoles' => 'Miércoles',
            'Jueves' => 'Jueves',
            'Viernes' => 'Viernes',
            'Sábado' => 'Sábado',
            'Domingo' => 'Domingo'
        ];
    }
}
