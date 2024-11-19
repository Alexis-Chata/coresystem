<?php

namespace App\Livewire;

use App\Models\Empresa;
use App\Models\FTipoDocumento;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Blade;
use Livewire\WithFileUploads;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class EmpresaTable extends PowerGridComponent
{
    use WithFileUploads;

    public string $tableName = 'empresa-table-xzunoc-table';
    public bool $showCreateForm = false;
    public $empleado;
    public $user;
    public $productionSelectOptions = [0 => '0-Desarrollo', 1 => '1-Produccion'];
    public $newEmpresa = [
        "ruc" => '',
        "razon_social" => '',
        "name_comercial" => '',
        "direccion" => '',
        "logo_path" => '',
        "cert_path" => '',
        "sol_user" => '',
        "sol_pass" => '',
        "client_id" => '',
        "client_secret" => '',
        "production" => '',
    ];

    public function setUp(): array
    {
        $this->user = auth()->user();
        $this->empleado = $this->user->empleados()->first();

        return [
            PowerGrid::header()
                ->showSearchInput()
                ->includeViewOnTop('components.create-empresa-form'),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount()
                ->pageName('empresaPage'),
        ];
    }

    public function datasource(): Builder
    {
        return Empresa::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('ruc')
            ->add('razon_social')
            ->add('name_comercial')
            ->add('direccion')
            ->add('logo_path_url')
            ->add('cert_path')
            ->add('sol_user')
            ->add('sol_pass')
            ->add('client_id')
            ->add('client_secret')
            ->add('production', function ($empresa) {
                if ($this->user->can('edit empresa')) {
                    return $this->selectComponent('production', $empresa->id, $empresa->production, $this->productionSelectOptions);
                }
                return $empresa->cliente_nombre;
            })
            ->add('created_at')
            ->add('created_at_formatted', function ($empresa) {
                return Carbon::parse($empresa->created_at)->format('d/m/Y H:i'); //20/01/2024 10:05
            });
    }

    private function selectComponent($field, $empresaId, $selected, $options)
    {
        return Blade::render(
            '<select wire:change="updateField(\'' . $field . '\', $event.target.value, ' . $empresaId . ')">'
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

    public function updateField($field, $value, $empresaId)
    {
        $this->onUpdatedEditable($empresaId, $field, $value);
        $this->dispatch('pg:eventRefresh-default');
    }

    public function onUpdatedEditable(string|int $id, string $field, string $value): void
    {
        Empresa::query()->find($id)->update([
            $field => e($value),
        ]);
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Ruc', 'ruc')
                ->sortable()
                ->searchable()
                ->editOnClick(),

            Column::make('Razon social', 'razon_social')
                ->sortable()
                ->searchable()
                ->editOnClick(),

            Column::make('Name comercial', 'name_comercial')
                ->sortable()
                ->searchable()
                ->editOnClick(),

            Column::make('Direccion', 'direccion')
                ->sortable()
                ->searchable()
                ->editOnClick(),

            Column::make('Logo path', 'logo_path')
                ->sortable()
                ->searchable(),

            Column::make('Cert path', 'cert_path')
                ->sortable()
                ->searchable(),

            Column::make('Sol user', 'sol_user')
                ->sortable()
                ->searchable()
                ->editOnClick(),

            Column::make('Sol pass', 'sol_pass')
                ->sortable()
                ->searchable()
                ->editOnClick(),

            Column::make('Client id', 'client_id')
                ->sortable()
                ->searchable()
                ->editOnClick(),

            Column::make('Client secret', 'client_secret')
                ->sortable()
                ->searchable()
                ->editOnClick(),

            Column::make('Production', 'production')
                ->sortable()
                ->searchable(),

            Column::make('Created at', 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [];
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert(' . $rowId . ')');
    }

    public function actions(Empresa $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit: ' . $row->id)
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

    public function createEmpresa()
    {
        $messages = [
            'newEmpresa.ruc.required' => '* Obligatorio',
            'newEmpresa.ruc.unique' => 'Ya existe una empresa con este RUC',
            'newEmpresa.ruc.regex' => 'El RUC debe tener 11 dígitos',
            'newEmpresa.razon_social.required' => '* Obligatorio',
            'newEmpresa.name_comercial.required' => '* Obligatorio',
            'newEmpresa.direccion.required' => '* Obligatorio',
            'newEmpresa.logo_path.mimes' => '* Formato no válido',
            'newEmpresa.cert_path.required' => '* Obligatorio',
            'newEmpresa.cert_path.mimes' => '* Formato no válido',
            'newEmpresa.sol_user.required' => '* Obligatorio',
            'newEmpresa.sol_pass.required' => '* Obligatorio',
            //'newEmpresa.client_id.required' => '* Obligatorio',
            //'newEmpresa.client_secret.required' => '* Obligatorio',
            'newEmpresa.production.required' => '* Obligatorio',
        ];

        $this->validate([
            'newEmpresa.ruc' => ['required', 'unique:empresas,ruc', 'regex:/^(10|20)\d{9}$/'],
            'newEmpresa.razon_social' => 'required',
            'newEmpresa.name_comercial' => 'required',
            'newEmpresa.direccion' => 'required',
            'newEmpresa.logo_path' => 'nullable|file|mimes:png,jpg,jpeg',
            'newEmpresa.cert_path' => 'required|file|extensions:pem,txt,PEM,TXT',
            'newEmpresa.sol_user' => 'required',
            'newEmpresa.sol_pass' => 'required',
            //'newEmpresa.client_id' => 'required',
            //'newEmpresa.client_secret' => 'required',
            'newEmpresa.production' => 'required',
        ], $messages);

        if ($this->newEmpresa['logo_path']) {
            $this->newEmpresa['logo_path'] = $this->newEmpresa['logo_path']->store('logos');
        }
        $this->newEmpresa['cert_path'] = $this->newEmpresa['cert_path']->store('certificados');
        Empresa::create($this->newEmpresa);

        $this->reset('newEmpresa');
        $this->dispatch('pg:eventRefresh-default');
        $this->dispatch('SweetAlert2', 'Cliente creado exitosamente');
        $this->dispatch('refresh-sede-table');
    }
}
