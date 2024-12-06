<?php

namespace App\Livewire;

use App\Models\Pedido;
use Carbon\Carbon;
use Livewire\Component;

class PedidoReporteDiario extends Component
{
    public $fecha;

    public function mount()
    {
        $this->fecha = now()->format('Y-m-d');
    }

    public function updatedFecha()
    {
        $this->dispatch('fecha-updated');
    }

    public function render()
    {
        $user = auth()->user();
        $fechaBusqueda = Carbon::parse($this->fecha)->format('d-m-Y');
        
        if ($user->hasRole('admin')) {
            $pedidosPorVendedor = Pedido::where('fecha_emision', $fechaBusqueda)
                ->with(['vendedor', 'ruta', 'cliente', 'pedidoDetalles.producto'])
                ->get()
                ->groupBy('vendedor_id');
        } else {
            $vendedorId = $user->empleados->first()->id;
            $pedidosPorVendedor = Pedido::where('fecha_emision', $fechaBusqueda)
                ->where('vendedor_id', $vendedorId)
                ->with(['ruta', 'cliente', 'pedidoDetalles.producto'])
                ->get()
                ->groupBy('ruta_id');
        }

        return view('livewire.pedido-reporte-diario', [
            'pedidosPorVendedor' => $pedidosPorVendedor
        ]);
    }
}