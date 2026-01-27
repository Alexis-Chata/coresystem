<?php

namespace App\Livewire;

use App\Models\Marca;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule as ValidationRule;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Facades\Rule; // <- para actionRules()

final class MarcaTable extends PowerGridComponent
{
    public string $tableName = 'marca-table-kwk1j9-table';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput()
                ->includeViewOnTop('components.create-marca-form'),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public bool $showCreateForm = false;

    public $newMarca = [
        'codigo' => '',
        'name' => '',
        'empresa_id' => '',
        'resaltar_cobertura' => false,
        'color_identificador' => '#4b96e1',
    ];

    public function openCreateForm()
    {
        $this->showCreateForm = true;
    }

    public function closeCreateForm()
    {
        $this->showCreateForm = false;
        $this->reset('newMarca');
    }

    public function createMarca()
    {
        $this->validate([
            'newMarca.codigo' => 'required|unique:marcas,codigo',
            'newMarca.name' => 'required|max:255',
            'newMarca.empresa_id' => 'required|exists:empresas,id',
            'newMarca.resaltar_cobertura' => 'boolean',
            'newMarca.color_identificador' => ['nullable', 'max:20', 'regex:/^#([0-9A-Fa-f]{6}|[0-9A-Fa-f]{3})$/'],
        ]);

        $marca = Marca::create($this->newMarca);
        $marca->nro_orden = $marca->id;
        $marca->save();

        $this->reset('newMarca');
        $this->showCreateForm = false;

        $this->dispatch('pg:eventRefresh-default');
        $this->dispatch('marca-created', 'Marca creada exitosamente');
    }
    public function datasource(): Builder
    {
        return Marca::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('codigo')
            ->add('name')
            ->add('empresa_id')
            ->add('resaltar_cobertura')
            ->add('color_identificador')
            ->add('color_preview', function (Marca $m) {
                $hex = (string) ($m->color_identificador ?? '#000000');

                // Sanitizar por seguridad
                if (!preg_match('/^#([0-9A-Fa-f]{6}|[0-9A-Fa-f]{3})$/', $hex)) {
                    $hex = '#000000';
                }

                $hexEsc = e($hex);

                return '<span style="display:inline-flex;align-items:center;gap:6px">
                            <span style="width:14px;height:14px;border-radius:4px;background:' . $hexEsc . ';border:1px solid #999"></span>
                            <span>' . $hexEsc . '</span>
                        </span>';
            })
            ->add('created_at');
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Codigo', 'codigo')
                ->sortable()
                ->searchable()
                ->editOnClick(),

            Column::make('Name', 'name')
                ->sortable()
                ->searchable()
                ->editOnClick(),

            Column::make('Empresa id', 'empresa_id')
                ->sortable()
                ->searchable()
                ->editOnClick(),

            // ✅ Toggle (switch)
            Column::make('Resaltar cobertura', 'resaltar_cobertura')
                ->toggleable(true, 'Sí', 'No'),

            // 👁️ Preview (HTML). Para búsqueda/orden usa dataField.
            Column::make('Color (preview)', 'color_preview', 'color_identificador'),

            // ✏️ Editable (hex)
            Column::make('Color (hex)', 'color_identificador')
                ->sortable()
                ->searchable()
                ->editOnClick(),

            Column::action('Action'),
        ];
    }


    // Opcional: resaltar la fila cuando resaltar_cobertura = true
    public function actionRules(): array
    {
        return [
            Rule::rows()
                ->when(fn(Marca $m) => (bool) $m->resaltar_cobertura)
                // Si tu tema es Bootstrap suele funcionar:
                ->setAttribute('class', 'table-warning'),
            // Si usas Tailwind/daisyUI, cambia a algo como:
            // ->setAttribute('class', '!bg-yellow-100'),
        ];
    }

    // Funcion para actualizar los campos editables
    public function onUpdatedEditable(string|int $id, string $field, string $value): void
    {
        $allowed = ['codigo', 'name', 'empresa_id', 'color_identificador'];
        if (!in_array($field, $allowed, true)) {
            return;
        }

        $value = trim($value);

        // Validación por campo
        $rules = match ($field) {
            'codigo' => [
                'value' => ['required', 'max:50', ValidationRule::unique('marcas', 'codigo')->ignore($id)],
            ],
            'name' => [
                'value' => ['required', 'max:255'],
            ],
            'empresa_id' => [
                'value' => ['required', 'integer', 'exists:empresas,id'],
            ],
            'color_identificador' => [
                'value' => ['nullable', 'max:20', 'regex:/^#([0-9A-Fa-f]{6}|[0-9A-Fa-f]{3})$/'],
            ],
            default => ['value' => ['nullable']],
        };

        Validator::make(['value' => $value], $rules)->validate();

        // Casts / normalización
        if ($field === 'empresa_id') {
            $value = (int) $value;
        }

        Marca::query()->whereKey($id)->update([
            $field => $value,
        ]);

        // Si quieres evitar re-render completo:
        // $this->skipRender();
    }
    // Fin de la funcion para actualizar los campos editables


    // Actualiza toggles (toggleable switch)
    public function onUpdatedToggleable(string|int $id, string $field, string $value): void
    {
        if ($field !== 'resaltar_cobertura') {
            return;
        }

        $bool = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $bool = $bool ?? ((int) $value === 1);

        Marca::query()->whereKey($id)->update([
            $field => $bool,
        ]);

        // $this->skipRender();
    }

    public function actions(Marca $row): array
    {
        return [
            // Funcion para eliminar una marca, se debe crear una funcion en el componente hijo para eliminar la marca.
            Button::add('delete')
                ->slot('Eliminar')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('deleteMarca', ['marcaId' => $row->id])
            // Fin de la funcion para eliminar una marca
        ];
    }

    // Componente hijo para eliminar una marca
    #[\Livewire\Attributes\On('deleteMarca')]
    public function deleteMarca($marcaId): void
    {
        Marca::destroy($marcaId);
    }
    // Fin del componente hijo para eliminar una marca

}
