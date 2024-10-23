<?php

namespace App\Livewire;

use App\Models\Empleado;
use App\Models\Empresa;
use App\Models\F_tipo_documento;
use App\Models\Vehiculo;
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

final class EmpleadoTable extends PowerGridComponent
{
    public string $tableName = 'empleado-table-pdipqg-table';
    public bool $showCreateForm = false;

    public $newEmpleado = [
        'codigo' => '',
        'name' => '',
        'direccion' => '',
        'celular' => '',
        'f_tipo_documento_id' => '',
        'numero_documento' => '',
        'tipo_empleado' => '',
        'numero_brevete' => '',
        'empresa_id' => '',
        'vehiculo_id' => '',
    ];

    public function setUp(): array
    {
        //$this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput()
                ->includeViewOnTop('components.create-empleado-form'),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Empleado::query()
            ->join('f_tipo_documentos', 'empleados.f_tipo_documento_id', '=', 'f_tipo_documentos.id')
            ->join('empresas', 'empleados.empresa_id', '=', 'empresas.id')
            ->leftJoin('vehiculos', 'empleados.vehiculo_id', '=', 'vehiculos.id')
            ->select('empleados.*', 
                     'f_tipo_documentos.name as tipo_documento_nombre',
                     'empresas.razon_social as empresa_nombre',
                     'vehiculos.placa as vehiculo_placa');
    }

    public function relationSearch(): array
    {
        return [
            'tipoDocumento' => ['name'],
            'empresa' => ['razon_social'],
            'vehiculo' => ['placa'],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('codigo')
            ->add('name')
            ->add('direccion')
            ->add('celular')
            ->add('f_tipo_documento_id', function ($empleado) {
                return $this->selectComponent('f_tipo_documento_id', $empleado->id, $empleado->f_tipo_documento_id, $this->tipoDocumentoSelectOptions());
            })
            ->add('numero_documento')
            ->add('tipo_empleado')
            ->add('numero_brevete')
            ->add('empresa_id', function ($empleado) {
                return $this->selectComponent('empresa_id', $empleado->id, $empleado->empresa_id, $this->empresaSelectOptions());
            })
            ->add('vehiculo_id', function ($empleado) {
                return $this->selectComponent('vehiculo_id', $empleado->id, $empleado->vehiculo_id, $this->vehiculoSelectOptions());
            })
            ->add('created_at_formatted', fn (Empleado $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'));
    }

    private function selectComponent($field, $empleadoId, $selected, $options)
    {
        return Blade::render(
            '<select wire:change="updateField(\''. $field .'\', $event.target.value, '. $empleadoId .')">'
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
            Column::make('Código', 'codigo')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Nombre', 'name')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Dirección', 'direccion')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Celular', 'celular')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Tipo de Documento', 'f_tipo_documento_id')
                ->sortable(),
            Column::make('Número de Documento', 'numero_documento')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Tipo de Empleado', 'tipo_empleado')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Número de Brevete', 'numero_brevete')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Empresa', 'empresa_id')
                ->sortable(),
            Column::make('Vehículo', 'vehiculo_id')
                ->sortable(),
            Column::action('Acción')
        ];
    }

    public function filters(): array
    {
        return [
            Filter::datetimepicker('created_at'),
        ];
    }

    public function actions(Empleado $row): array
    {
        return [
            Button::add('delete')
                ->slot('Eliminar')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('deleteEmpleado', ['empleadoId' => $row->id])
        ];
    }

    #[On('deleteEmpleado')]
    public function deleteEmpleado($empleadoId): void
    {
        Empleado::destroy($empleadoId);
    }

    public function onUpdatedEditable(string|int $id, string $field, string $value): void
    {
        Empleado::query()->find($id)->update([
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
        $this->reset('newEmpleado');
    }

    public function createEmpleado()
    {
        $this->validate([
            'newEmpleado.codigo' => 'required',
            'newEmpleado.name' => 'required',
            'newEmpleado.f_tipo_documento_id' => 'required|exists:f_tipo_documentos,id',
            'newEmpleado.numero_documento' => 'required',
            'newEmpleado.tipo_empleado' => 'required',
            'newEmpleado.empresa_id' => 'required|exists:empresas,id',
        ]);

        Empleado::create($this->newEmpleado);

        $this->reset('newEmpleado');
        $this->dispatch('pg:eventRefresh-default');
        $this->dispatch('empleado-created', 'Empleado creado exitosamente');
    }

    public function tipoDocumentoSelectOptions()
    {
        return F_tipo_documento::all()->pluck('name', 'id');
    }

    public function empresaSelectOptions()
    {
        return Empresa::all()->pluck('razon_social', 'id');
    }

    public function vehiculoSelectOptions()
    {
        return Vehiculo::all()->pluck('placa', 'id');
    }

    #[On('updateField')]
    public function updateField($field, $value, $empleadoId)
    {
        $empleado = Empleado::find($empleadoId);
        if ($empleado) {
            $empleado->update([$field => $value]);
            $this->dispatch('pg:eventRefresh-default');
        }
    }
}