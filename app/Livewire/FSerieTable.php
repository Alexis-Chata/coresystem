<?php

namespace App\Livewire;

use App\Livewire\Forms\FserieForm;
use App\Models\FSede;
use App\Models\Fserie;
use App\Models\FTipoComprobante;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class FSerieTable extends PowerGridComponent
{
    public string $tableName = 'f-serie-table-2trzfa-table';
    public bool $showCreateForm = false;
    public $empleado;
    public $user;
    public $f_sedes = [];
    public $f_tipo_comprobantes = [];
    public $productionSelectOptions = [0 => '0-Desarrollo', 1 => '1-Produccion'];
    public $newFSerie1 = [
        "serie" => '',
        "correlativo" => '',
        "fechaemision" => '',
        "f_sede_id" => '',
        "f_tipo_comprobante_id" => '',
    ];
    public FserieForm $newFSerie;

    public function setUp(): array
    {
        $this->f_sedes = FSede::all();
        $this->f_tipo_comprobantes = FTipoComprobante::all();

        return [
            PowerGrid::header()
                ->showSearchInput()
                ->includeViewOnTop('components.create-fserie-form'),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount()
                ->pageName('fSeriePage'),
        ];
    }

    public function datasource(): Builder
    {
        return Fserie::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('serie')
            ->add('correlativo')
            ->add('fechaemision')
            ->add('fechaemision_formatted', function ($row) {
                return Carbon::parse($row->created_at)->format('d/m/Y'); //20/01/2024
            })
            ->add('f_sede_id', fn($row) => $row->fSede->name)
            ->add('f_tipo_comprobante_id', fn($row) => $row->fTipoComprobante->name)
            ->add('created_at')
            ->add('created_at_formatted', function ($row) {
                return Carbon::parse($row->created_at)->format('d/m/Y H:i'); //20/01/2024 10:05
            });
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Serie', 'serie')
                ->sortable()
                ->searchable(),

            Column::make('Correlativo', 'correlativo')
                ->sortable()
                ->searchable(),

            Column::make('Fechaemision', 'fechaemision_formatted', 'fechaemision')
                ->sortable()
                ->searchable(),

            Column::make('Sede', 'f_sede_id'),
            Column::make('Tipo comprobante', 'f_tipo_comprobante_id'),
            Column::make('Created at', 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [
        ];
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert('.$rowId.')');
    }

    public function actions(Fserie $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit: '.$row->id)
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('edit', ['rowId' => $row->id])
        ];
    }

    /*
    public function actionRules($row): array
    {
        return [
            // Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn($row) => $row->id === 1)
                ->hide(),
        ];
    }
    */

    public function createFSerie()
    {
        $this->newFSerie->store();
    }
}
