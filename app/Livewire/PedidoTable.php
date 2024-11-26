<?php

namespace App\Livewire;

use App\Models\Pedido;
use App\Models\Ruta;
use App\Models\Empleado;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\ListaPrecio;
use App\Models\Producto;
use Illuminate\Support\Carbon;
use Livewire\Component;

class PedidoTable extends Component
{
    // Propiedades del formulario
    public $empresa;
    public $fecha_emision;
    public $vendedor_id;
    public $cliente_id = '';
    public $ruta_id = '';
    public $lista_precio = '';
    public $direccion = '';
    public $documento = '';
    public $search = '';
    public $productos = [];
    public $pedido_detalles = [];
    public $cantidad = 1;

    // Propiedades para listas y usuario
    public $clientes = [];
    public $vendedores = [];
    public $empleado;
    public $user;

    public function mount()
    {
        $this->user = auth()->user();
        $this->empleado = $this->user->empleados()->first();
        $this->pedido_detalles = [];
        
        // Inicializar datos por defecto
        $this->initializeDefaultData();
        
        // Cargar datos según el rol
        $this->loadDataByRole();
    }

    private function initializeDefaultData()
    {
        $this->empresa = Empresa::first();
        $this->fecha_emision = Carbon::now()->format('d-m-Y');
        
        if (!$this->user->hasRole('admin')) {
            $this->vendedor_id = $this->empleado->id;
        }
    }

    private function loadDataByRole()
    {
        if ($this->user->hasRole('admin')) {
            $this->vendedores = Empleado::where('tipo_empleado', 'vendedor')->get();
            $this->clientes = Cliente::all();
        } else {
            $rutasDelVendedor = Ruta::where('vendedor_id', $this->empleado->id)->pluck('id');
            $this->clientes = Cliente::whereIn('ruta_id', $rutasDelVendedor)->get();
        }
    }

    public function updatedClienteId($value)
    {
        if (!$value) {
            $this->resetClienteData();
            return;
        }

        $cliente = Cliente::with(['ruta', 'listaPrecio', 'tipoDocumento'])->find($value);
        
        if ($cliente) {
            $this->updateClienteData($cliente);
        }
    }

    private function resetClienteData()
    {
        $this->direccion = '';
        $this->ruta_id = '';
        $this->lista_precio = '';
        $this->documento = '';
    }

    private function updateClienteData($cliente)
    {
        $this->direccion = $cliente->direccion;
        $this->ruta_id = $cliente->ruta_id;
        $this->lista_precio = $cliente->lista_precio_id;
        $this->documento = $cliente->tipoDocumento->tipo_documento . ' - ' . $cliente->numero_documento;
    }

    public function getRutaNameProperty()
    {
        return $this->ruta_id ? optional(Ruta::find($this->ruta_id))->name : '';
    }

    public function getListaPrecioNameProperty()
    {
        return $this->lista_precio ? optional(ListaPrecio::find($this->lista_precio))->name : '';
    }

    public function updatedSearch()
    {
        if (!$this->lista_precio) {
            return;
        }

        if (strlen($this->search) > 0) {
            $this->productos = Producto::where(function($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('id', 'like', '%' . $this->search . '%');
                })
                ->with([
                    'marca',
                    'listaPrecios' => function($query) {
                        $query->where('lista_precio_id', $this->lista_precio);
                    }
                ])
                ->take(5)
                ->get();

            // Debug para verificar los precios
            logger('Productos encontrados:', [
                'lista_precio' => $this->lista_precio,
                'productos' => $this->productos->map(function($producto) {
                    return [
                        'id' => $producto->id,
                        'name' => $producto->name,
                        'precio' => $producto->listaPrecios->first()?->pivot?->precio
                    ];
                })
            ]);
        } else {
            $this->productos = [];
        }
    }

    public function agregarProducto($producto_id)
    {
        if (!$this->lista_precio) {
            // Mostrar mensaje de error o alerta
            return;
        }

        $producto = Producto::with([
            'listaPrecios' => function($query) {
                $query->where('lista_precio_id', $this->lista_precio);
            }
        ])->find($producto_id);
        
        if (!$producto) return;

        // Verificar si el producto ya existe en el detalle
        $existe = collect($this->pedido_detalles)->first(function ($detalle) use ($producto_id) {
            return $detalle['producto_id'] === $producto_id;
        });

        if (!$existe) {
            $precio = $producto->listaPrecios->first()?->pivot?->precio ?? 0;

            if ($precio > 0) {
                $this->pedido_detalles[] = [
                    'producto_id' => $producto->id,
                    'codigo' => $producto->id,
                    'nombre' => $producto->name,
                    'cantidad' => $this->cantidad,
                    'importe' => $precio * $this->cantidad
                ];
            }
        }

        // Limpiar búsqueda
        $this->search = '';
        $this->productos = [];
    }

    public function eliminarDetalle($index)
    {
        unset($this->pedido_detalles[$index]);
        $this->pedido_detalles = array_values($this->pedido_detalles);
    }

    public function actualizarCantidad($index)
    {
        $detalle = $this->pedido_detalles[$index];
        $producto = Producto::find($detalle['producto_id']);
        
        $precio = $producto->listaPrecios()
            ->where('lista_precio_id', $this->lista_precio)
            ->first()
            ->pivot
            ->precio ?? 0;

        $this->pedido_detalles[$index]['importe'] = $precio * $this->pedido_detalles[$index]['cantidad'];
    }

    public function render()
    {
        return view('livewire.pedido-table');
    }
    
}
