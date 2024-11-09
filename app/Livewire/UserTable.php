<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\Facades\Rule;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class UserTable extends PowerGridComponent
{
    public string $tableName = 'user-table-txj4ck-table';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSoftDeletes()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return User::query()->with(['user_empleado', 'empresa', 'user_empleado.empleado']);
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('email')
            ->add('name_vendedor', fn($user) => e(optional(optional($user->user_empleado)->empleado)->id . ' - ' . optional(optional($user->user_empleado)->empleado)->name))
            ->add('empresa', fn($user) => e($user->empresa->razon_social))
            ->add('created_at_formatted', function ($user) {
                return Carbon::parse($user->created_at)->format('d/m/Y H:i'); //20/01/2024 10:05
            })
            ->add('deleted_at_formatted', function ($user) {
                return $user->deleted_at ? Carbon::parse($user->deleted_at)->format('d/m/Y H:i') : ''; //20/01/2024 10:05
            });
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Nombre Usuario', 'name')
                ->sortable()
                ->searchable(),
            Column::make('Email', 'email')
                ->sortable()
                ->searchable(),
            Column::make('Cod - Nombre Vendedor', 'name_vendedor')
                ->sortable()
                ->searchable(),
            Column::make('Empresa', 'empresa'),
            Column::make('Registrado', 'created_at_formatted')
                ->sortable(),
            Column::make('Eliminado', 'deleted_at_formatted')
                ->sortable(),

            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [];
    }

    #[\Livewire\Attributes\On('restoreUser')]
    public function restore($rowId): void
    {
        $user = User::withTrashed()->find($rowId);
        if ($user) {
            $user->restore();

            $this->dispatch('pg:eventRefresh-default');
            $this->dispatch('user-SweetAlert2', 'Restaurado exitosamente');
        }
    }

    #[\Livewire\Attributes\On('deleteUser')]
    public function delete($rowId): void
    {
        $user = User::find($rowId);
        if ($user) {
            $user->delete(); // Soft delete

            $this->dispatch('pg:eventRefresh-default');
            $this->dispatch('user-SweetAlert2', 'Eliminado exitosamente');
        }
    }

    public function actions(User $row): array
    {
        $actions = [];

        $actions[] = Button::add('restore')
            ->slot('Restaurar')
            ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
            ->dispatch('restoreUser', ['rowId' => $row->id]);

        $actions[] = Button::add('delete')
            ->slot('Eliminar')
            ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
            ->dispatch('deleteUser', ['rowId' => $row->id]);

        return $actions;
    }

    public function actionRules($row): array
    {
        return [
            Rule::button('restore')
                ->when(fn($row) => !$row->trashed())
                ->hide(),
            Rule::button('delete')
                ->when(fn($row) => $row->id == auth_id())
                ->hide(),
            Rule::button('delete')
                ->when(fn($row) => $row->trashed())
                ->hide(),
        ];
    }
}
