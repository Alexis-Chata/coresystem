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
    public $empleado;
    public $user;

    public $newPadron = [
        'cliente_id' => '',
        'ruta_id' => '',
        'nro_secuencia' => '',
    ];

    public function setUp(): array
    {
        //$this->showCheckBox();
        $this->user = auth()->user();
        $this->empleado = $this->user->empleados()->first();

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
        $query = Padron::query()
            ->join('clientes', 'padrons.cliente_id', '=', 'clientes.id')
            ->join('rutas', 'padrons.ruta_id', '=', 'rutas.id')
            ->select('padrons.*', 'clientes.razon_social as cliente_nombre', 'rutas.name as ruta_nombre');

        // OJO: Filtro registros por rol de administrador o vendedor
        $empleado = $this->empleado;
        if ($this->user->hasRole("admin")) {
        } elseif ($this->user->hasRole("vendedor")) {
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
        $var_clienteSelectOptions = $this->clienteSelectOptions();
        $var_rutaSelectOptions = $this->rutaSelectOptions();
        return PowerGrid::fields()
            ->add('id')
            ->add('cliente_id', function ($padron) use ($var_clienteSelectOptions) {
                return $this->selectComponent('cliente_id', $padron->id, $padron->cliente_id, $var_clienteSelectOptions);
            })
            ->add('cliente_id', function ($padron) use ($var_clienteSelectOptions) {
                if ($this->user->can('edit padron')) {
                    return $this->selectComponent('cliente_id', $padron->id, $padron->cliente_id, $var_clienteSelectOptions);
                }
                return $padron->cliente_nombre;
            })
            ->add('ruta_id', function ($padron) use ($var_rutaSelectOptions) {
                return $this->selectComponent('ruta_id', $padron->id, $padron->ruta_id, $var_rutaSelectOptions);
            })
            ->add('vendedor_id', function (Padron $model) {
                return $model->ruta->vendedor_id.' - '.$model->ruta->vendedor->name;
            })
            ->add('nro_secuencia')
            ->add('created_at_formatted', fn(Padron $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'))
            ->add('estado', function (Padron $model) {
                return $model->deleted_at ? 'Eliminado' : 'Activo';
            })
            ->add('deleted_at_formatted', fn(Padron $model) => $model->deleted_at ? Carbon::parse($model->deleted_at)->format('d/m/Y H:i:s') : null);
    }

    private function selectComponent($field, $padronId, $selected, $options)
    {
        return Blade::render(
            '<select wire:change="updateField(\'' . $field . '\', $event.target.value, ' . $padronId . ')">'
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
            Column::make('Nro. Sec.', 'nro_secuencia')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Cliente', 'cliente_id')
                ->sortable(),
            Column::make('Estado', 'estado'),
        ];

        // Agregar la columna solo si el usuario es admin
        //if ($this->user->can('view menuEmpleado')) {
        //$columns[] = Column::make('Fecha de eliminación', 'deleted_at_formatted')
        //->sortable();
        //}

        // Agregar la columna solo si el usuario es admin
        if ($this->user->hasRole("admin")) {
        $columns[] = Column::make('Vendedor', 'vendedor_id')
        ->sortable();
        }

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
            if ($this->user->can('delete padron')) {
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
            $rutaId = $padron->ruta_id;
            $padron->delete(); // Soft delete

            // Reorganizar las secuencias solo para la misma ruta
            Padron::where('ruta_id', $rutaId)
                ->where('nro_secuencia', '>', $secuenciaEliminada)
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
            // Obtener la última secuencia de la ruta específica
            $ultimaSecuencia = Padron::where('ruta_id', $padron->ruta_id)->max('nro_secuencia');

            // Restaurar con la siguiente secuencia disponible en esa ruta
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

            // Reorganizar secuencias solo dentro de la misma ruta
            if ($newSequence > $oldSequence) {
                Padron::where('ruta_id', $padron->ruta_id)
                    ->where('nro_secuencia', '>', $oldSequence)
                    ->where('nro_secuencia', '<=', $newSequence)
                    ->decrement('nro_secuencia');
            } else if ($newSequence < $oldSequence) {
                Padron::where('ruta_id', $padron->ruta_id)
                    ->where('nro_secuencia', '>=', $newSequence)
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
        $empleado = $this->empleado;
        $query = Ruta::query();

        if ($empleado && $this->user->hasRole("vendedor")) {
            $query->where('rutas.vendedor_id', $empleado->id);
        }

        return $query->pluck('name', 'id');
    }

    #[On('updateField')]
    public function updateField($field, $value, $padronId)
    {
        $padron = Padron::find($padronId);
        if ($padron) {
            if ($field === 'ruta_id') {
                $oldRutaId = $padron->ruta_id;
                $ruta = Ruta::find($value);
                $secuenciaActual = $padron->nro_secuencia;

                if ($ruta) {
                    // Primero, reorganizar la ruta anterior
                    Padron::where('ruta_id', $oldRutaId)
                        ->where('nro_secuencia', '>', $secuenciaActual)
                        ->decrement('nro_secuencia');

                    // Verificar si la secuencia ya existe en la nueva ruta
                    $existeSecuencia = Padron::where('ruta_id', $value)
                        ->where('nro_secuencia', $secuenciaActual)
                        ->exists();

                    if ($existeSecuencia) {
                        // Si existe, mover todos los números de secuencia mayores o iguales hacia arriba
                        Padron::where('ruta_id', $value)
                            ->where('nro_secuencia', '>=', $secuenciaActual)
                            ->increment('nro_secuencia');
                    }

                    // Actualizar el padrón con la misma secuencia
                    $padron->update([
                        'ruta_id' => $value,
                        'lista_precio_id' => $ruta->lista_precio_id,
                        'nro_secuencia' => $secuenciaActual
                    ]);

                    // Actualizar también el cliente asociado
                    if ($padron->cliente) {
                        $padron->cliente->update([
                            'ruta_id' => $value,
                            'lista_precio_id' => $ruta->lista_precio_id
                        ]);
                    }

                    // Asegurarse de que las secuencias estén ordenadas correctamente
                    $this->reorderSequences($value);
                }
            } else {
                // Para otros campos, actualizar normalmente
                $padron->update([$field => $value]);

                if ($padron->cliente && in_array($field, ['ruta_id', 'lista_precio_id'])) {
                    $padron->cliente->update([$field => $value]);
                }
            }

            $this->dispatch('pg:eventRefresh-default');
            $this->dispatch('refresh-cliente-table');
        }
    }

    // Agregar este nuevo método para asegurar el orden correcto de las secuencias
    private function reorderSequences($rutaId)
    {
        $padrones = Padron::where('ruta_id', $rutaId)
            ->orderBy('nro_secuencia')
            ->get();

        $secuencia = 1;
        foreach ($padrones as $padron) {
            if ($padron->nro_secuencia != $secuencia) {
                $padron->update(['nro_secuencia' => $secuencia]);
            }
            $secuencia++;
        }
    }

    // Evento para actualizar la tabla de padrón cuando se crea un cliente
    #[On('refresh-padron-table')]
    public function refreshTable(): void
    {
        $this->dispatch('pg:eventRefresh-default');
        $this->setUp();
    }

    public function filters(): array
    {
        $empleado = $this->empleado;
        $query = Ruta::query()
            ->whereIn('id', function ($subquery) use ($empleado) {
                $subquery->select('ruta_id')
                    ->from('padrons')
                    ->distinct();

                if ($empleado && $this->user->hasRole("vendedor")) {
                    $subquery->where('vendedor_id', $empleado->id);
                }
            });

        if ($empleado && $this->user->hasRole("vendedor")) {
            $query->where('vendedor_id', $empleado->id);
        }

        return [
            Filter::select('ruta_id', 'rutas.id')
                ->dataSource($query->get())
                ->optionLabel('name')
                ->optionValue('id'),
        ];
    }
}
