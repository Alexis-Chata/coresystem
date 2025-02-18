<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Cliente;
use App\Models\Ruta;
use Illuminate\Support\Facades\Log;

class ClienteSelect extends Component
{
    public $vendedor_id = null;
    public $cliente_id = null;
    public $search = '';
    public $clientes = [];
    public $showDropdown = false;

    protected $listeners = [
        'reset-cliente-select' => 'clearSelection'
    ];

    public function mount($vendedor_id = null, $cliente_id = null)
    {
        $this->vendedor_id = $vendedor_id;
        $this->cliente_id = $cliente_id;

        if ($this->cliente_id) {
            $cliente = Cliente::find($this->cliente_id);
            if ($cliente) {
                $this->search = $cliente->razon_social;
            }
        }
    }

    public function loadClientes()
    {
        if (!$this->vendedor_id) {
            return;
        }

        $rutasDelVendedor = Ruta::where('vendedor_id', $this->vendedor_id)->pluck('id');
        //Log::info('Rutas del vendedor', ['rutas' => $rutasDelVendedor]);

        $query = Cliente::whereIn('ruta_id', $rutasDelVendedor);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('razon_social', 'like', '%' . $this->search . '%')
                    ->orWhere('id', 'like', '%' . $this->search . '%');
            });
        }

        $this->clientes = $query->get();
        $this->showDropdown = true;

        Log::info('Clientes cargados', [
            'search' => $this->search,
            'count' => count($this->clientes)
        ]);
    }

    public function showAll()
    {
        $this->loadClientes();
    }

    public function updatedSearch()
    {
        if (!$this->vendedor_id) {
            Log::info('Búsqueda sin vendedor seleccionado');
            $this->clientes = [];
            $this->showDropdown = false;
            return;
        }

        $this->loadClientes();
    }

    public function selectCliente($clienteId)
    {
        $this->resetValidation();
        $cliente = Cliente::find($clienteId);
        if ($cliente) {
            $this->cliente_id = $cliente->id;
            $this->search = $cliente->razon_social;
            $this->dispatch('cliente-selected', $cliente->id);
        }
        $this->showDropdown = false;
        Log::info('Cliente seleccionado', ['cliente_id' => $clienteId]);
    }

    public function clearSelection()
    {
        $this->cliente_id = null;
        $this->search = '';
        $this->loadClientes();
        $this->dispatch('cliente-selected', null);
        //$this->dispatch('dropdown-opened');
        Log::info('Selección limpiada');
    }

    public function render()
    {
        return view('livewire.cliente-select');
    }
}
