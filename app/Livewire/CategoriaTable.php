<?php

namespace App\Livewire;

use App\Models\Categoria;
use App\Models\Empresa;
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

final class CategoriaTable extends PowerGridComponent
{
    public string $tableName = 'categoria-table-fhibwx-table';
    public bool $showCreateForm = false;

    public $newCategoria = [
        'nombre' => '',
        'descripcion' => '',
        'empresa_id' => '',
    ];

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput()
                ->includeViewOnTop('components.create-categoria-form'),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Categoria::query()
            ->join('empresas', 'categorias.empresa_id', '=', 'empresas.id')
            ->select('categorias.*', 'empresas.razon_social as empresa_nombre');
    }

    public function relationSearch(): array
    {
        return [
            'empresa' => ['razon_social'],
        ];
    }

    public function fields(): PowerGridFields
    {
        $empresaOptions = $this->empresaSelectOptions();

        return PowerGrid::fields()
            ->add('id')
            ->add('nombre')
            ->add('descripcion')
            ->add('empresa_id', function ($categoria) use ($empresaOptions) {
                return $this->selectComponent('empresa_id', $categoria->id, $categoria->empresa_id, $empresaOptions);
            })
            ->add('created_at_formatted', fn (Categoria $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'));
    }

    private function selectComponent($field, $categoriaId, $selected, $options)
    {
        return Blade::render(
            '<select wire:change="updateField(\''. $field .'\', $event.target.value, '. $categoriaId .')">'
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
            Column::make('Nombre', 'nombre')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Descripción', 'descripcion')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Empresa', 'empresa_id')
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

    public function actions(Categoria $row): array
    {
        return [
            Button::add('delete')
                ->slot('Eliminar')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('deleteCategoria', ['categoriaId' => $row->id])
        ];
    }

    #[On('deleteCategoria')]
    public function deleteCategoria($categoriaId): void
    {
        Categoria::destroy($categoriaId);
    }

    public function onUpdatedEditable(string|int $id, string $field, string $value): void
    {
        Categoria::query()->find($id)->update([
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
        $this->reset('newCategoria');
    }

    public function createCategoria()
    {
        $this->validate([
            'newCategoria.nombre' => 'required',
            'newCategoria.empresa_id' => 'required|exists:empresas,id',
        ]);

        Categoria::create($this->newCategoria);

        $this->reset('newCategoria');
        $this->dispatch('pg:eventRefresh-default');
        $this->dispatch('categoria-created', 'Categoría creada exitosamente');
    }

    public function empresaSelectOptions()
    {
        return Empresa::all()->pluck('razon_social', 'id');
    }

    #[On('updateField')]
    public function updateField($field, $value, $categoriaId)
    {
        $categoria = Categoria::find($categoriaId);
        if ($categoria) {
            $categoria->update([$field => $value]);
            $this->dispatch('pg:eventRefresh-default');
        }
    }
}