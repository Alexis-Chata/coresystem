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
    public string $sortField = 'nro_secuencia'; 
    public string $sortDirection = 'asc';

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
                ->includeViewOnTop('components.create-padron-form')
                ->showSoftDeletes(showMessage: true),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount()
                ->pageName('padronPage'),
        ];
    }

    public function datasource(): Builder
    {
        $empleado = auth()->user()->empleados()->first();

        $query = Padron::query()
            ->join('clientes', 'padrons.cliente_id', '=', 'clientes.id')
            ->join('rutas', 'padrons.ruta_id', '=', 'rutas.id')
            ->select('padrons.*', 'clientes.razon_social as cliente_nombre', 'rutas.name as ruta_nombre');

        if ($empleado && $empleado->tipo_empleado === 'vendedor') {
            $query->where('rutas.vendedor_id', $empleado->id);
        }

        return $query;
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
            ->add('created_at_formatted', fn (Padron $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'))
            ->add('estado', function (Padron $model) {
                return $model->deleted_at ? 'Eliminado' : 'Activo';
            })
            ->add('deleted_at_formatted', fn (Padron $model) => $model->deleted_at ? Carbon::parse($model->deleted_at)->format('d/m/Y H:i:s') : null);
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
        $columns = [
            Column::make('Ruta', 'ruta_id')
                ->sortable(),
            Column::make('Nro. Secuencia', 'nro_secuencia')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Cliente', 'cliente_id')
                ->sortable(),
            Column::make('Estado', 'estado')
                ->sortable()
                ->bodyAttribute('class', 'text-center'),
        ];

        // Agregar la columna solo si el usuario es admin
        //if (auth()->user()->can('view menuEmpleado')) {
            //$columns[] = Column::make('Fecha de eliminación', 'deleted_at_formatted')
                //->sortable();
        //}

        $columns[] = Column::action('Acción');

        return $columns;
    }

    public function actions(Padron $row): array
    {
        $actions = [];
        
        if ($row->deleted_at) {
            $actions[] = Button::add('restore')
                ->slot('Restaurar')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('restorePadron', ['padronId' => $row->id]);
            if (auth()->user()->can('view menuEmpleado')) {
                $actions[] = Button::add('forceDelete')
                    ->slot('Eliminar permanentemente')
                    ->id()
                    ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                    ->dispatch('forceDeletePadron', ['padronId' => $row->id]);
            }
        } else {
            $actions[] = Button::add('delete')
                ->slot('Eliminar')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('deletePadron', ['padronId' => $row->id]);
        }
        
        return $actions;
    }

    #[On('deletePadron')]
    public function deletePadron($padronId): void
    {
        $padron = Padron::find($padronId);
        if ($padron) {
            $secuenciaEliminada = $padron->nro_secuencia;
            $padron->delete(); // Soft delete

            // Reorganizar las secuencias después de eliminar
            Padron::where('nro_secuencia', '>', $secuenciaEliminada)
                  ->decrement('nro_secuencia');

            $this->dispatch('pg:eventRefresh-default');
            $this->dispatch('padron-deleted', 'Padrón eliminado exitosamente');
        }
    }

    #[On('restorePadron')]
    public function restorePadron($padronId): void
    {
        $padron = Padron::withTrashed()->find($padronId);
        if ($padron) {
            // Obtener la última secuencia
            $ultimaSecuencia = Padron::max('nro_secuencia');
            
            // Restaurar con la siguiente secuencia disponible
            $padron->nro_secuencia = $ultimaSecuencia + 1;
            $padron->restore();
            
            $this->dispatch('pg:eventRefresh-default');
            $this->dispatch('padron-restored', 'Padrón restaurado exitosamente');
        }
    }

    #[On('forceDeletePadron')]
    public function forceDeletePadron($padronId): void
    {
        $padron = Padron::withTrashed()->find($padronId);
        if ($padron) {
            $padron->forceDelete();
            $this->dispatch('pg:eventRefresh-default');
            $this->dispatch('padron-force-deleted', 'Padrón eliminado permanentemente');
        }
    }

    public function onUpdatedEditable(string|int $id, string $field, string $value): void
    {
        if ($field === 'nro_secuencia') {
            $padron = Padron::find($id);
            $oldSequence = $padron->nro_secuencia;
            $newSequence = (int)$value;

            // Si la nueva secuencia es mayor que la anterior
            if ($newSequence > $oldSequence) {
                Padron::where('nro_secuencia', '>', $oldSequence)
                      ->where('nro_secuencia', '<=', $newSequence)
                      ->decrement('nro_secuencia');
            }
            // Si la nueva secuencia es menor que la anterior
            else if ($newSequence < $oldSequence) {
                Padron::where('nro_secuencia', '>=', $newSequence)
                      ->where('nro_secuencia', '<', $oldSequence)
                      ->increment('nro_secuencia');
            }

            $padron->update(['nro_secuencia' => $newSequence]);
        } else {
            Padron::query()->find($id)->update([
                $field => $value,
            ]);
        }
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
        $empleado = auth()->user()->empleados()->first();
        $query = Ruta::query();
        
        if ($empleado && $empleado->tipo_empleado === 'vendedor') {
            $query->where('rutas.vendedor_id', $empleado->id);
        }
        
        return $query->pluck('name', 'id');
    }

    #[On('updateField')]
    public function updateField($field, $value, $padronId)
    {
        $padron = Padron::find($padronId);
        if ($padron) {
            // Si el campo que se está actualizando es ruta_id
            if ($field === 'ruta_id') {
                // Obtener la ruta y su lista de precios asociada
                $ruta = Ruta::find($value);
                if ($ruta) {
                    // Actualizar tanto la ruta como la lista de precios en padrón
                    $padron->update([
                        'ruta_id' => $value,
                        'lista_precio_id' => $ruta->lista_precio_id
                    ]);

                    // Actualizar también el cliente asociado
                    if ($padron->cliente) {
                        $padron->cliente->update([
                            'ruta_id' => $value,
                            'lista_precio_id' => $ruta->lista_precio_id
                        ]);
                    }
                }
            } else {
                // Para otros campos, actualizar normalmente
                $padron->update([$field => $value]);
                
                // Si el campo existe en la tabla clientes, actualizarlo también
                if ($padron->cliente && in_array($field, ['ruta_id', 'lista_precio_id'])) {
                    $padron->cliente->update([$field => $value]);
                }
            }

            $this->dispatch('pg:eventRefresh-default');
            // Disparar evento para actualizar la tabla de clientes
            $this->dispatch('refresh-cliente-table');
        }
    }

    // Evento para actualizar la tabla de padrón cuando se crea un cliente
    #[On('refresh-padron-table')]
    public function refreshTable(): void
    {
        $this->dispatch('pg:eventRefresh-default');
    }
}
