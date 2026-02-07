<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Cliente;
use App\Models\Ruta;
use Illuminate\Support\Facades\Log;

use function Illuminate\Log\log;

class ClienteSelect extends Component
{
    public $vendedor_id = null;
    public $cliente_id = null;

    // Esto ya NO se usa para buscar en server; solo para mostrar el seleccionado al renderizar
    public $search = '';

    // Opciones ya listas para filtrar en JS (sin requests)
    public $clientesOptions = [];

    protected $listeners = [
        'reset-cliente-select' => 'clearSelection',
    ];

    public function mount($vendedor_id = null, $cliente_id = null)
    {
        $this->vendedor_id = $vendedor_id;
        $this->cliente_id  = $cliente_id;

        if ($this->cliente_id) {
            $cliente = Cliente::select('id', 'razon_social')->find($this->cliente_id);
            if ($cliente) {
                $this->search = $cliente->razon_social;
            }
        }

        $this->loadClientesOptions(); // ✅ carga una vez
    }

    public function updatedVendedorId()
    {
        // Cuando cambia vendedor, reset y recarga options
        $this->cliente_id = null;
        $this->search = '';
        $this->dispatch('cliente-selected', null);

        $this->loadClientesOptions();
    }

    public function loadClientesOptions()
    {
        if (!$this->vendedor_id) {
            $this->clientesOptions = [];
            return;
        }

        $rutasDelVendedor = Ruta::where('vendedor_id', $this->vendedor_id)->pluck('id');

        $inicioMes = now()->startOfMonth();
        $finMes    = now()->endOfMonth();

        $clientes = Cliente::query()
            ->with([
                'listaPrecio:id,name',
                'fComprobanteSunats' => function ($q) use ($inicioMes, $finMes) {
                    $q->where('estado_reporte', 1)
                        ->whereBetween('pedido_fecha_factuacion', [$inicioMes, $finMes])
                        ->with(['detalle.producto.marca:id,name,resaltar_cobertura,color_identificador']);
                },
            ])
            ->whereIn('ruta_id', $rutasDelVendedor)
            ->orderBy('razon_social')
            ->get(['id', 'razon_social', 'lista_precio_id']);

        // ✅ Precalcular marcas acá (ya NO en Blade)
        $this->clientesOptions = $clientes->map(function ($c) {
            $marcas = collect($c->fComprobanteSunats ?? [])
                ->flatMap(fn($comp) => collect($comp->detalle ?? []))
                ->map(fn($det) => $det->producto?->marca)
                ->filter(fn($m) => $m && (int) $m->resaltar_cobertura === 1)
                ->unique('id')
                ->values()
                ->map(function ($m) {
                    $hex = $m->color_identificador ?: '#000000';
                    if (!preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/', $hex)) $hex = '#000000';

                    return [
                        'id'    => $m->id,
                        'name'  => $m->name,
                        'color' => $hex,
                    ];
                })
                ->values()
                ->all();

            return [
                'id'         => $c->id,
                'name'       => $c->razon_social,
                'listaPrecio' => $c->listaPrecio?->name ?? '',
                'marcas'     => $marcas, // array [{name,color},...]
            ];
        })->values()->all();

        Log::info('Clientes cargados', [
            'search' => $this->search,
            'count' => count($this->clientesOptions)
        ]);
    }

    public function selectCliente($clienteId)
    {
        $cliente = Cliente::select('id', 'razon_social')->find($clienteId);

        if ($cliente) {
            $this->cliente_id = $cliente->id;
            $this->search     = $cliente->razon_social;

            $this->dispatch('cliente-selected', $cliente->id);
        }

        $this->dispatch('cliente-dropdown-close');
    }

    public function clearSelection()
    {
        $this->cliente_id = null;
        $this->search = '';

        $this->dispatch('cliente-selected', null);
        $this->dispatch('cliente-dropdown-open');
    }


    public function render()
    {
        return view('livewire.cliente-select');
    }
}
