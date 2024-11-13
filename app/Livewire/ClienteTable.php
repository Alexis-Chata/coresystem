<?php

namespace App\Livewire;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\FTipoDocumento;
use App\Models\ListaPrecio;
use App\Models\Ruta;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Responsive;
use Illuminate\Support\Facades\Blade;
use Livewire\Attributes\On;

final class ClienteTable extends PowerGridComponent
{
    public string $tableName = 'cliente-table-ctgosb-table';
    public bool $showCreateForm = false;

    public $newCliente = [
        'razon_social' => '',
        'direccion' => '',
        'f_tipo_documento_id' => '',
        'numero_documento' => '',
        'celular' => '',
        'empresa_id' => '',
        'lista_precio_id' => '',
        'ruta_id' => '',
    ];

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->showSearchInput()
                ->includeViewOnTop('components.create-cliente-form'),
            PowerGrid::footer()
                ->showPerPage(5, [5, 10, 15, 20, 0])
                ->showRecordCount()
                ->pageName('clientePage'),
            PowerGrid::responsive()
                ->fixedColumns('id', 'razon_social'),
        ];
    }

    public function datasource(): Builder
    {
        $query = Cliente::query()
            ->join('f_tipo_documentos', 'clientes.f_tipo_documento_id', '=', 'f_tipo_documentos.id')
            ->join('empresas', 'clientes.empresa_id', '=', 'empresas.id')
            ->join('lista_precios', 'clientes.lista_precio_id', '=', 'lista_precios.id')
            ->join('rutas', 'clientes.ruta_id', '=', 'rutas.id')
            ->select(
                'clientes.*',
                'f_tipo_documentos.tipo_documento as tipo_documento_nombre',
                'empresas.razon_social as empresa_nombre',
                'lista_precios.name as lista_precio_nombre',
                'rutas.name as ruta_nombre'
            );

        // OJO: Filtro registros por vendedor
        $empleado = auth()->user()->empleados()->first();
        if ($empleado && $empleado->tipo_empleado === 'vendedor') {
            $query->where('rutas.vendedor_id', $empleado->id);
        }

        return $query;
    }

    public function relationSearch(): array
    {
        return [
            'tipoDocumento' => ['tipo_documento'],
            'empresa' => ['razon_social'],
            'listaPrecio' => ['name'],
        ];
    }

    public function fields(): PowerGridFields
    {
        $tipoDocumentoOptions = $this->tipoDocumentoSelectOptions();
        $empresaOptions = $this->empresaSelectOptions();
        $listaPrecioOptions = $this->listaPrecioSelectOptions();
        $rutaOptions = $this->rutaSelectOptions();

        return PowerGrid::fields()
            ->add('id')
            ->add('id_formatted', fn(Cliente $model) => str_pad($model->id, 8, '0', STR_PAD_LEFT))
            ->add('razon_social')
            ->add('direccion')
            ->add('f_tipo_documento_id', function ($cliente) use ($tipoDocumentoOptions) {
                return $this->selectComponent('f_tipo_documento_id', $cliente->id, $cliente->f_tipo_documento_id, $tipoDocumentoOptions);
            })
            ->add('numero_documento')
            ->add('celular')
            ->add('empresa_id', function ($cliente) use ($empresaOptions) {
                return $this->selectComponent('empresa_id', $cliente->id, $cliente->empresa_id, $empresaOptions);
            })
            ->add('lista_precio_nombre')
            ->add('ruta_nombre')
            ->add('created_at_formatted', fn(Cliente $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'));
    }

    private function selectComponent($field, $clienteId, $selected, $options)
    {
        return Blade::render(
            '<select wire:change="updateField(\'' . $field . '\', $event.target.value, ' . $clienteId . ')">'
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
            Column::make('Cod', 'id_formatted'),
            Column::make('Razón social', 'razon_social')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Dirección', 'direccion')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Tipo Doc.', 'f_tipo_documento_id')
                ->sortable()
                ->searchable(),
            Column::make('Número Doc.', 'numero_documento')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Celular', 'celular')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Ruta', 'ruta_nombre')
                ->sortable(),
            Column::make('Lista precio', 'lista_precio_nombre')
                ->sortable(),
            Column::action('Acción')
        ];
    }

    public function onUpdatedEditable(string|int $id, string $field, string $value): void
    {
        Cliente::query()->find($id)->update([
            $field => $value,
        ]);
        $this->editingField = null;
        $this->dispatch('pg:closeEditor-default');

        // Agregar esta línea para actualizar la tabla de padrón
        $this->dispatch('refresh-padron-table');
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
        // Establecer valores por defecto si no están presentes
        if (empty($this->newCliente['f_tipo_documento_id'])) {
            $primerTipoDocumento = FTipoDocumento::first();
            $this->newCliente['f_tipo_documento_id'] = $primerTipoDocumento ? $primerTipoDocumento->id : null;
        }

        if (empty($this->newCliente['numero_documento'])) {
            $this->newCliente['numero_documento'] = '99999999';
        }

        $messages = [
            'newCliente.razon_social.required' => 'La razón social es obligatoria',
            'newCliente.f_tipo_documento_id.required' => 'El tipo de documento es obligatorio',
            'newCliente.f_tipo_documento_id.exists' => 'El tipo de documento seleccionado no es válido',
            'newCliente.numero_documento.required' => 'El número de documento es obligatorio',
            //'newCliente.numero_documento.unique' => 'Este número de documento ya está registrado',
            'newCliente.empresa_id.required' => 'Debe seleccionar una empresa',
            'newCliente.empresa_id.exists' => 'La empresa seleccionada no es válida',
            'newCliente.lista_precio_id.required' => 'Debe seleccionar una lista de precios',
            'newCliente.lista_precio_id.exists' => 'La lista de precios seleccionada no es válida',
            'newCliente.ruta_id.required' => 'Debe seleccionar una ruta',
            'newCliente.ruta_id.exists' => 'La ruta seleccionada no es válida',
        ];

        $this->validate([
            'newCliente.razon_social' => 'required',
            'newCliente.f_tipo_documento_id' => 'required|exists:f_tipo_documentos,id',
            'newCliente.numero_documento' => 'required',
            'newCliente.empresa_id' => 'required|exists:empresas,id',
            'newCliente.lista_precio_id' => 'required|exists:lista_precios,id',
            'newCliente.ruta_id' => 'required|exists:rutas,id',
        ], $messages);

        Cliente::create($this->newCliente);

        $this->reset('newCliente');
        $this->dispatch('pg:eventRefresh-default');
        $this->dispatch('cliente-created', 'Cliente creado exitosamente');
        // Disparar evento para actualizar la tabla de padrón
        $this->dispatch('refresh-padron-table');
    }

    public function tipoDocumentoSelectOptions()
    {
        return FTipoDocumento::all()->pluck('tipo_documento', 'id');
    }

    public function empresaSelectOptions()
    {
        return Empresa::all()->pluck('razon_social', 'id');
    }

    public function listaPrecioSelectOptions()
    {
        return ListaPrecio::all()->pluck('name', 'id');
    }

    public function rutaSelectOptions()
    {
        return Ruta::all()->pluck('name', 'id');
    }

    #[On('updateField')]
    public function updateField($field, $value, $clienteId)
    {
        $cliente = Cliente::find($clienteId);
        if ($cliente) {
            $cliente->update([$field => $value]);
            $this->dispatch('pg:eventRefresh-default');

            // Agregar esta línea para actualizar la tabla de padrón
            $this->dispatch('refresh-padron-table');
        }
    }

    #[On('edit-field')]
    public function editField($field, $id): void
    {
        if ($this->editingField && $this->editingField !== $field . '_' . $id) {
            $this->dispatch('pg:closeEditor-default');
        }
        $this->editingField = $field . '_' . $id;
    }

    #[On('ruta-selected')]
    public function handleRutaSelected($rutaId)
    {
        // Obtener la lista de precios asociada a la ruta
        $ruta = Ruta::find($rutaId);
        if ($ruta) {
            $this->newCliente['lista_precio_id'] = $ruta->lista_precio_id;
        }
    }

    #[On('refresh-cliente-table')]
    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-default');
    }
}
