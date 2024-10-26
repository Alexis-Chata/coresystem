<?php

namespace App\Livewire;

use App\Models\Padron;
use App\Models\Cliente;
use App\Models\Ruta;
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

final class PadronTable extends PowerGridComponent
{
    public string $tableName = 'padron-table-ay3rv1-table';
    public bool $showCreateForm = false;

    public $newPadron = [
        'cliente_id' => '',
        'ruta_id' => '',
        'nro_secuencia' => '',
    ];

    public function setUp(): array
    {
        //$this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput()
                ->includeViewOnTop('components.create-padron-form'),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Padron::query()
            ->join('clientes', 'padrons.cliente_id', '=', 'clientes.id')
            ->join('rutas', 'padrons.ruta_id', '=', 'rutas.id')
            ->select('padrons.*', 'clientes.razon_social as cliente_nombre', 'rutas.name as ruta_nombre');
    }

    public function relationSearch(): array
    {
        return [
            'cliente' => ['razon_social'],
            'ruta' => ['name'],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('cliente_id', function ($padron) {
                return $this->selectComponent('cliente_id', $padron->id, $padron->cliente_id, $this->clienteSelectOptions());
            })
            ->add('ruta_id', function ($padron) {
                return $this->selectComponent('ruta_id', $padron->id, $padron->ruta_id, $this->rutaSelectOptions());
            })
            ->add('nro_secuencia')
            ->add('created_at_formatted', fn (Padron $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'));
    }

    private function selectComponent($field, $padronId, $selected, $options)
    {
        return Blade::render(
            '<select wire:change="updateField(\''. $field .'\', $event.target.value, '. $padronId .')">'
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
            //Column::make('Id', 'id'),
            Column::make('Ruta', 'ruta_id')
                ->sortable(),
            Column::make('Nro. Secuencia', 'nro_secuencia')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Cliente', 'cliente_id')
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

    public function actions(Padron $row): array
    {
        return [
            Button::add('delete')
                ->slot('Eliminar')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('deletePadron', ['padronId' => $row->id])
        ];
    }

    #[On('deletePadron')]
    public function deletePadron($padronId): void
    {
        Padron::destroy($padronId);
    }

    public function onUpdatedEditable(string|int $id, string $field, string $value): void
    {
        Padron::query()->find($id)->update([
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
        $this->reset('newPadron');
    }

    public function createPadron()
    {
        $this->validate([
            'newPadron.cliente_id' => 'required|exists:clientes,id',
            'newPadron.ruta_id' => 'required|exists:rutas,id',
            'newPadron.nro_secuencia' => 'required|integer',
        ]);

        Padron::create($this->newPadron);

        $this->reset('newPadron');
        $this->dispatch('pg:eventRefresh-default');
        $this->dispatch('padron-created', 'Padrón creado exitosamente');
    }

    public function clienteSelectOptions()
    {
        return Cliente::all()->pluck('razon_social', 'id');
    }

    public function rutaSelectOptions()
    {
        return Ruta::all()->pluck('name', 'id');
    }

    #[On('updateField')]
    public function updateField($field, $value, $padronId)
    {
        $padron = Padron::find($padronId);
        if ($padron) {
            $padron->update([$field => $value]);
            $this->dispatch('pg:eventRefresh-default');
        }
    }
}