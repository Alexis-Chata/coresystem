<?php

namespace App\Livewire;

use App\Models\Pedido;
use App\Models\Ruta;
use App\Models\F_tipo_comprobante;
use App\Models\Empleado;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Lista_precio;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Rules\Rule;
use Livewire\Component;

final class PedidoTable extends PowerGridComponent
{
    public string $tableName = 'pedidos';

    public bool $showModalForm = false;
    
    // Propiedades para el formulario
    public $ruta_id;
    public $f_tipo_comprobante_id;
    public $vendedor_id;
    public $conductor_id;
    public $cliente_id;
    public $fecha_emision;
    public $importe_total;
    public $nro_doc_liquidacion;
    public $lista_precio;  // Asegúrate de que esta propiedad esté definida
    public $empresa_id;

    // Propiedades para los selectores
    public $rutas = [];
    public $tiposComprobante = [];
    public $vendedores = [];
    public $conductores = [];
    public $clientes = [];
    public $empresas = [];
    public $listaPrecios = [];

    public function mount(): void
    {
        $this->rutas = Ruta::all();
        $this->tiposComprobante = F_tipo_comprobante::all();
        $this->vendedores = Empleado::where('tipo_empleado', 'vendedor')->get();
        $this->conductores = Empleado::where('tipo_empleado', 'conductor')->get();
        $this->clientes = Cliente::all();
        $this->empresas = Empresa::all();
        $this->listaPrecios = Lista_precio::all();

        parent::mount();
    }

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput()
                ->includeViewOnTop('components.create-button'),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Pedido::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('ruta_id')
            ->add('f_tipo_comprobante_id')
            ->add('vendedor_id')
            ->add('conductor_id')
            ->add('cliente_id')
            ->add('fecha_emision')
            ->add('importe_total')
            ->add('nro_doc_liquidacion')
            ->add('lista_precio')
            ->add('empresa_id')
            ->add('created_at');
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Ruta id', 'ruta_id')
                ->editOnClick(),
            Column::make('F tipo comprobante id', 'f_tipo_comprobante_id')
                ->editOnClick(),
            Column::make('Vendedor id', 'vendedor_id')
                ->editOnClick(),
            Column::make('Conductor id', 'conductor_id')
                ->editOnClick(),
            Column::make('Cliente id', 'cliente_id')
                ->editOnClick(),
            Column::make('Fecha emision', 'fecha_emision')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Importe total', 'importe_total')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Nro doc liquidacion', 'nro_doc_liquidacion')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Lista precio id', 'lista_precio')
                ->sortable()
                ->searchable()
                ->editOnClick(),
            Column::make('Empresa id', 'empresa_id')
                ->editOnClick(),
            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [
        ];
    }

    public function onUpdatedEditable($id, $field, $value): void
    {
        Pedido::query()->find($id)->update([
            $field => $value,
        ]);
    }

    public function openModal(): void
    {
        $this->showModalForm = true;
    }

    public function closeModal(): void
    {
        $this->showModalForm = false;
        $this->reset(['ruta_id', 'f_tipo_comprobante_id', 'vendedor_id', 'conductor_id', 'cliente_id', 'fecha_emision', 'importe_total', 'nro_doc_liquidacion', 'lista_precio', 'empresa_id']);
    }

    public function addPedido(): void
    {
        $this->validate([
            'ruta_id' => 'required|exists:rutas,id',
            'f_tipo_comprobante_id' => 'required|exists:f_tipo_comprobantes,id',
            'vendedor_id' => 'required|exists:empleados,id',
            'conductor_id' => 'required|exists:empleados,id',
            'cliente_id' => 'required|exists:clientes,id',
            'fecha_emision' => 'required|date',
            'importe_total' => 'required|numeric',
            'nro_doc_liquidacion' => 'required',
            'lista_precio' => 'required|exists:lista_precios,id',
            'empresa_id' => 'required|exists:empresas,id',
        ]);

        try {
            Pedido::create([
                'ruta_id' => $this->ruta_id,
                'f_tipo_comprobante_id' => $this->f_tipo_comprobante_id,
                'vendedor_id' => $this->vendedor_id,
                'conductor_id' => $this->conductor_id,
                'cliente_id' => $this->cliente_id,
                'fecha_emision' => $this->fecha_emision,
                'importe_total' => $this->importe_total,
                'nro_doc_liquidacion' => $this->nro_doc_liquidacion,
                'lista_precio' => $this->lista_precio,
                'empresa_id' => $this->empresa_id,
            ]);

            $this->closeModal();
            $this->emit('refreshDatatable');
            $this->dispatch('showAlert', ['message' => 'Pedido creado con éxito', 'type' => 'success']);
        } catch (\Exception $e) {
            $this->dispatch('showAlert', ['message' => 'Error al crear el pedido: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function actions(Pedido $row): array
    {
        return [
            Button::add('delete')
                ->slot('Eliminar')
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('delete', ['rowId' => $row->id])
        ];
    }

    #[\Livewire\Attributes\On('delete')]
    public function delete($rowId): void
    {
        Pedido::destroy($rowId);
    }
}