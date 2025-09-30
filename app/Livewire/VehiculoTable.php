<?php

namespace App\Livewire;

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

final class VehiculoTable extends PowerGridComponent
{
    public string $tableName = 'vehiculo-table';
    public bool $showCreateForm = false;

    public $newVehiculo = [
        'marca' => '',
        'modelo' => '',
        'placa' => '',
        'color' => '',
        'certificado_inscripcion' => '',
        'numero_tarjeta' => '',
        'tonelaje_maximo' => '',
    ];

    public function setUp(): array
    {
        $this->showCheckBox();

        $header = PowerGrid::header()
            ->showSearchInput();

        if (auth()->user()->can('delete vehiculo')) {
            $header->showSoftDeletes(showMessage: true);
        }

        if (auth()->user()->can('create vehiculo')) {
            $header->includeViewOnTop('components.create-vehiculo-form');
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
        return Vehiculo::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('marca')
            ->add('modelo')
            ->add('placa')
            ->add('color')
            ->add('certificado_inscripcion')
            ->add('numero_tarjeta')
            ->add('tonelaje_maximo')
            ->add('created_at_formatted', fn (Vehiculo $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'));
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Marca', 'marca')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Modelo', 'modelo')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Placa', 'placa')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Color', 'color')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Certificado Inscripción', 'certificado_inscripcion')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Número Tarjeta', 'numero_tarjeta')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Tonelaje Máximo', 'tonelaje_maximo')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::action('Acción')
                ->visibleInExport(false)
                ->hidden(!auth()->user()->can('delete vehiculo')),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::datetimepicker('created_at'),
        ];
    }

    public function actions(Vehiculo $row): array
    {
        $actions = [];

        if ($row->deleted_at) {
            $actions[] = Button::add('restore')
                ->slot('Restaurar')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('restoreVehiculo', ['vehiculoId' => $row->id])
                ->can(auth()->user()->can('delete vehiculo'));

            $actions[] = Button::add('force-delete')
                ->slot('Eliminar Permanentemente')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('forceDeleteVehiculo', ['vehiculoId' => $row->id])
                ->can(auth()->user()->can('delete vehiculo'));
        } else {
            $actions[] = Button::add('delete')
                ->slot('Eliminar')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('deleteVehiculo', ['vehiculoId' => $row->id])
                ->can(auth()->user()->can('delete vehiculo'));
        }

        return $actions;
    }

    #[On('deleteVehiculo')]
    public function deleteVehiculo($vehiculoId): void
    {
        $vehiculo = Vehiculo::find($vehiculoId);
        if ($vehiculo) {
            $vehiculo->delete();
            $this->dispatch('pg:eventRefresh-default');
            $this->dispatch('vehiculo-deleted', 'Vehículo eliminado exitosamente');
        }
    }

    #[On('restoreVehiculo')]
    public function restoreVehiculo($vehiculoId): void
    {
        $vehiculo = Vehiculo::withTrashed()->find($vehiculoId);
        if ($vehiculo) {
            $vehiculo->restore();
            $this->dispatch('pg:eventRefresh-default');
            $this->dispatch('vehiculo-restored', 'Vehículo restaurado exitosamente');
        }
    }

    #[On('forceDeleteVehiculo')]
    public function forceDeleteVehiculo($vehiculoId): void
    {
        $vehiculo = Vehiculo::withTrashed()->find($vehiculoId);
        if ($vehiculo) {
            $vehiculo->forceDelete();
            $this->dispatch('pg:eventRefresh-default');
            $this->dispatch('vehiculo-force-deleted', 'Vehículo eliminado permanentemente');
        }
    }

    public function onUpdatedEditable(string|int $id, string $field, string $value): void
    {
        Vehiculo::query()->find($id)->update([
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
        $this->reset('newVehiculo');
    }

    public function createVehiculo()
    {
        $this->validate([
            'newVehiculo.placa' => 'required',
        ]);

        Vehiculo::create($this->newVehiculo);

        $this->reset('newVehiculo');
        $this->dispatch('pg:eventRefresh-default');
        $this->dispatch('vehiculo-created', 'Vehículo creado exitosamente');
    }
}
