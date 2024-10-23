<?php

namespace App\Livewire;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\F_tipo_documento;
use App\Models\Lista_precio;
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

final class ClienteTable extends PowerGridComponent
{
    public string $tableName = 'cliente-table-ctgosb-table';
    public bool $showCreateForm = false;

    public $newCliente = [
        'razon_social' => '',
        'direccion' => '',
        'clientecol' => '',
        'f_tipo_documento_id' => '',
        'numero_documento' => '',
        'celular' => '',
        'empresa_id' => '',
        'lista_precio_id' => '',
    ];

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput()
                ->includeViewOnTop('components.create-cliente-form'),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
{
    return Cliente::query()
        ->join('f_tipo_documentos', 'clientes.f_tipo_documento_id', '=', 'f_tipo_documentos.id')
        ->join('empresas', 'clientes.empresa_id', '=', 'empresas.id')
        ->join('lista_precios', 'clientes.lista_precio_id', '=', 'lista_precios.id')
        ->select('clientes.*', 
                 'f_tipo_documentos.tipo_documento as tipo_documento_nombre', 
                 'empresas.razon_social as empresa_nombre',
                 'lista_precios.name as lista_precio_nombre');
}

    public function relationSearch(): array
    {
        return [
            'tipoDocumento' => ['tipo_documento'],
            'empresa' => ['razon_social'],
            'listaPrecio' => ['nombre'],
        ];
    }

    public function fields(): PowerGridFields
    {
        $tipoDocumentoOptions = $this->tipoDocumentoSelectOptions();
        $empresaOptions = $this->empresaSelectOptions();
        $listaPrecioOptions = $this->listaPrecioSelectOptions();

        return PowerGrid::fields()
            ->add('id')
            ->add('razon_social')
            ->add('direccion')
            ->add('clientecol')
            ->add('f_tipo_documento_id', function ($cliente) use ($tipoDocumentoOptions) {
                return $this->selectComponent('f_tipo_documento_id', $cliente->id, $cliente->f_tipo_documento_id, $tipoDocumentoOptions);
            })
            ->add('numero_documento')
            ->add('celular')
            ->add('empresa_id', function ($cliente) use ($empresaOptions) {
                return $this->selectComponent('empresa_id', $cliente->id, $cliente->empresa_id, $empresaOptions);
            })
            ->add('lista_precio_id', function ($cliente) use ($listaPrecioOptions) {
                return $this->selectComponent('lista_precio_id', $cliente->id, $cliente->lista_precio_id, $listaPrecioOptions);
            })
            ->add('created_at_formatted', fn (Cliente $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'));
    }

    private function selectComponent($field, $clienteId, $selected, $options)
    {
        return Blade::render(
            '<select wire:change="updateField(\''. $field .'\', $event.target.value, '. $clienteId .')">'
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
            Column::make('Razón social', 'razon_social')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Dirección', 'direccion')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Clientecol', 'clientecol')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Tipo documento', 'f_tipo_documento_id')
                ->sortable()
                ->searchable(),
            Column::make('Número documento', 'numero_documento')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Celular', 'celular')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Empresa', 'empresa_id')
                ->sortable()
                ->searchable(),
            Column::make('Lista precio', 'lista_precio_id')
                ->sortable()
                ->searchable(),
            Column::action('Acción')
        ];
    }

    public function onUpdatedEditable(string|int $id, string $field, string $value): void
    {
        Cliente::query()->find($id)->update([
            $field => $value,
        ]);
    }

    public function actions(Cliente $row): array
    {
        return [
            Button::add('delete')
                ->slot('Eliminar')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('deleteCliente', ['clienteId' => $row->id])
        ];
    }

    #[On('deleteCliente')]
    public function deleteCliente($clienteId): void
    {
        Cliente::destroy($clienteId);
    }

    public function openCreateForm()
    {
        $this->showCreateForm = true;
    }

    public function closeCreateForm()
    {
        $this->showCreateForm = false;
        $this->reset('newCliente');
    }

    public function createCliente()
    {
        $this->validate([
            'newCliente.razon_social' => 'required',
            'newCliente.f_tipo_documento_id' => 'required|exists:f_tipo_documentos,id',
            'newCliente.numero_documento' => 'required|unique:clientes,numero_documento',
            'newCliente.empresa_id' => 'required|exists:empresas,id',
            'newCliente.lista_precio_id' => 'required|exists:lista_precios,id',
        ]);

        Cliente::create($this->newCliente);

        $this->reset('newCliente');
        $this->dispatch('pg:eventRefresh-default');
        $this->dispatch('cliente-created', 'Cliente creado exitosamente');
    }

    public function tipoDocumentoSelectOptions()
    {
        return F_tipo_documento::all()->pluck('tipo_documento', 'id');
    }

    public function empresaSelectOptions()
    {
        return Empresa::all()->pluck('razon_social', 'id');
    }

    public function listaPrecioSelectOptions()
    {
        return Lista_precio::all()->pluck('name', 'id');
    }

    #[On('updateField')]
    public function updateField($field, $value, $clienteId)
    {
        $cliente = Cliente::find($clienteId);
        if ($cliente) {
            $cliente->update([$field => $value]);
            $this->dispatch('pg:eventRefresh-default');
        }
    }
}