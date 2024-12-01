<?php

namespace App\Livewire;

use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Ruta;
use App\Models\Empleado;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\ListaPrecio;
use App\Models\Producto;
use App\Models\FTipoComprobante;
use App\Traits\CalculosTrait;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PedidoTable extends Component
{
    use CalculosTrait;
    // Propiedades del formulario
    public $empresa;
    public $fecha_emision;
    public $vendedor_id;
    public $cliente_id = "";
    public $ruta_id = "";
    public $lista_precio = "";
    public $direccion = "";
    public $documento = "";
    public $search = "";
    public $productos = [];
    public $pedido_detalles = [];
    public $cantidad = 1;
    public $importe_total = 0;
    public $nro_doc_liquidacion;
    public $f_tipo_comprobante_id = "";
    public $tipoComprobantes = [];
    public $comentarios = "";  // Nueva propiedad para los comentarios

    // Propiedades para listas y usuario
    public $clientes = [];
    public $vendedores = [];
    public $empleado;
    public $user;

    protected $rules = [
        "cliente_id" => "required",
        "vendedor_id" => "required",
        "f_tipo_comprobante_id" => "required",
        "pedido_detalles" => "required|array|min:1",
    ];

    protected $messages = [
        "cliente_id.required" => "Debe seleccionar un cliente",
        "vendedor_id.required" => "Debe seleccionar un vendedor",
        "f_tipo_comprobante_id.required" =>
            "Debe seleccionar un tipo de comprobante",
        "pedido_detalles.required" => "Debe agregar al menos un producto",
        "pedido_detalles.min" => "Debe agregar al menos un producto",
    ];

    protected $listeners = [
        'cliente-selected' => 'handleClienteSelected'
    ];

    public function mount()
    {
        $this->user = auth()->user();
        $this->empleado = $this->user->empleados()->first();
        $this->pedido_detalles = [];

        // Inicializar datos por defecto
        $this->initializeDefaultData();

        // Cargar datos según el rol
        $this->loadDataByRole();
        $this->loadTipoComprobantes();
    }

    private function initializeDefaultData()
    {
        $this->empresa = Empresa::first();
        $this->fecha_emision = Carbon::now()->format("d-m-Y");

        if (!$this->user->hasRole("admin")) {
            $this->vendedor_id = $this->empleado->id;
        }
    }

    private function loadDataByRole()
    {
        if ($this->user->hasRole("admin")) {
            $this->vendedores = Empleado::where(
                "tipo_empleado",
                "vendedor"
            )->get();
            $this->clientes = collect([]); // Inicializar como colección vacía
        } else {
            $rutasDelVendedor = Ruta::where(
                "vendedor_id",
                $this->empleado->id
            )->pluck("id");
            $this->clientes = Cliente::whereIn(
                "ruta_id",
                $rutasDelVendedor
            )->get();
        }
    }

    private function loadTipoComprobantes()
    {
        if ($this->user->hasRole("admin")) {
            $this->tipoComprobantes = FTipoComprobante::all();
        } else {
            $this->tipoComprobantes = FTipoComprobante::where("estado",true)->get();
        }
    }

    public function updatedClienteId($value)
    {
        if (!$value) {
            $this->resetClienteData();
            return;
        }

        $cliente = Cliente::with([
            "ruta",
            "listaPrecio",
            "tipoDocumento",
        ])->find($value);

        if ($cliente) {
            $this->updateClienteData($cliente);
        }
    }

    private function resetClienteData()
    {
        $this->direccion = "";
        $this->ruta_id = "";
        $this->lista_precio = "";
        $this->documento = "";
    }

    private function updateClienteData($cliente)
    {
        $this->direccion = $cliente->direccion;
        $this->ruta_id = $cliente->ruta_id;
        $this->lista_precio = $cliente->lista_precio_id;
        $this->documento =
            $cliente->tipoDocumento->tipo_documento .
            " - " .
            $cliente->numero_documento;
    }

    public function getRutaNameProperty()
    {
        return $this->ruta_id ? optional(Ruta::find($this->ruta_id))->name : "";
    }

    public function getListaPrecioNameProperty()
    {
        return $this->lista_precio
            ? optional(ListaPrecio::find($this->lista_precio))->name
            : "";
    }

    public function updatedSearch()
    {
        if (!$this->lista_precio) {
            return;
        }

        if (strlen($this->search) > 0) {
            $this->productos = Producto::where(function ($query) {
                $query
                    ->where("name", "like", "%" . $this->search . "%")
                    ->orWhere("id", "like", "%" . $this->search . "%");
            })
                ->with([
                    "marca",
                    "listaPrecios" => function ($query) {
                        $query->where("lista_precio_id", $this->lista_precio);
                    },
                ])
                ->take(5)
                ->get();

            // Debug para verificar los precios
            logger("Productos encontrados:", [
                "lista_precio" => $this->lista_precio,
                "productos" => $this->productos->map(function ($producto) {
                    return [
                        "id" => $producto->id,
                        "name" => $producto->name,
                        "precio" => $producto->listaPrecios->first()?->pivot
                            ?->precio,
                    ];
                }),
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
            "listaPrecios" => function ($query) {
                $query->where("lista_precio_id", $this->lista_precio);
            },
        ])->find($producto_id);

        if (!$producto) {
            return;
        }

        // Verificar si el producto ya existe en el detalle
        $existe = collect($this->pedido_detalles)->first(function (
            $detalle
        ) use ($producto_id) {
            return $detalle["producto_id"] === $producto_id;
        });

        if (!$existe) {
            $precio = $producto->listaPrecios->first()?->pivot?->precio ?? 0;

            if ($precio > 0) {
                $this->pedido_detalles[] = [
                    "producto_id" => $producto->id,
                    "codigo" => $producto->id,
                    "nombre" => $producto->name,
                    "cantidad" => $this->cantidad,
                    "importe" => $precio * $this->cantidad,
                ];
            }
        }

        // Limpiar búsqueda
        $this->search = "";
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
        $producto = Producto::find($detalle["producto_id"]);

        $precio =
            $producto
                ->listaPrecios()
                ->where("lista_precio_id", $this->lista_precio)
                ->first()->pivot->precio ?? 0;

        $this->pedido_detalles[$index]["importe"] =
            $precio * $this->pedido_detalles[$index]["cantidad"];
        $this->calcularTotal();
    }

    private function calcularTotal()
    {
        $this->importe_total = collect($this->pedido_detalles)->sum("importe");
    }

    public function guardarPedido()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $this->importe_total = collect($this->pedido_detalles)->sum(
                "importe"
            );

            $pedido = Pedido::create([
                "ruta_id" => $this->ruta_id,
                "f_tipo_comprobante_id" => $this->f_tipo_comprobante_id,
                "vendedor_id" => $this->vendedor_id,
                "cliente_id" => $this->cliente_id,
                "fecha_emision" => $this->fecha_emision,
                "importe_total" => $this->importe_total,
                "nro_doc_liquidacion" => $this->nro_doc_liquidacion,
                "lista_precio" => $this->lista_precio,
                "empresa_id" => $this->empresa->id,
            ]);

            foreach ($this->pedido_detalles as $index => $detalle) {
                PedidoDetalle::create([
                    "pedido_id" => $pedido->id,
                    "item" => $index + 1,
                    "producto_id" => $detalle["producto_id"],
                    "producto_name" => $detalle["nombre"],
                    "cantidad" => $detalle["cantidad"],
                    "producto_precio" =>
                        $detalle["importe"] / $detalle["cantidad"],
                    "importe" => $detalle["importe"],
                    "comentario" => $this->comentarios, // Agregamos el comentario
                ]);
            }

            $subTotalesIgv = $this->setSubTotalesIgv($this->pedido_detalles);

            // Actualizar el pedido con el total final (por si acaso)
            $pedido->update([
                "importe_total" => $this->importe_total,
            ]);

            DB::commit();

            // Limpiar formulario
            $this->reset([
                "pedido_detalles",
                "cliente_id",
                "direccion",
                "ruta_id",
                "lista_precio",
                "documento",
                "importe_total",
                "nro_doc_liquidacion",
                "f_tipo_comprobante_id",
                "comentarios", // Agregar comentarios a la limpieza
            ]);

            $this->dispatch("pedido-guardado", "Pedido guardado exitosamente");
        } catch (\Exception $e) {
            DB::rollback();
            logger("Error al guardar pedido:", ["error" => $e->getMessage()]);
            $this->dispatch("error", "Error al guardar el pedido");
        }
    }

    // Agregar este método para mantener actualizado el total
    public function updatedPedidoDetalles()
    {
        $this->calcularTotal();
    }

    public function render()
    {
        return view("livewire.pedido-table");
    }

    public function calcularImporte($index)
    {
        $detalle = $this->pedido_detalles[$index];
        $producto = Producto::find($detalle['producto_id']);

        if ($producto) {
            $precioCaja = $producto->listaPrecios->where('id', $this->lista_precio)->first()->pivot->precio ?? 0;
            $cantidadProducto = $producto->cantidad; // Cantidad de productos por caja

            // Validar que la cantidad del producto no sea cero
            if ($cantidadProducto <= 0) {
                logger("Error: La cantidad del producto es cero o negativa para el producto:", [
                    "producto_id" => $producto->id,
                    "precioCaja" => $precioCaja,
                    "cantidadProducto" => $cantidadProducto,
                ]);
                $this->pedido_detalles[$index]['importe'] = 0;
                return;
            }

            // Calcular precio por paquete
            $precioPorPaquete = $precioCaja / $cantidadProducto; // 108.00 / 36 = 3.00

            // Interpretar la cantidad ingresada
            $cantidad = $detalle['cantidad']; // Cantidad ingresada en cajas y paquetes

            // Separar la cantidad en cajas y paquetes
            $cajas = floor($cantidad); // Parte entera representa las cajas
            $paquetes = round(($cantidad - $cajas) * 100); // Parte decimal convertida a paquetes

            // Validar que los paquetes no excedan la cantidad de productos por caja
            if ($paquetes >= $cantidadProducto) {
                logger("Error: La cantidad de paquetes no puede ser mayor o igual a la cantidad de productos por caja.", [
                    "cantidadIngresada" => $cantidad,
                    "paquetes" => $paquetes,
                    "cantidadProducto" => $cantidadProducto,
                ]);
                $this->pedido_detalles[$index]['importe'] = 0; // O puedes lanzar un mensaje de error
                return;
            }

            // Calcular cantidad total de paquetes
            $cantidadPaquetes = ($cajas * $cantidadProducto) + $paquetes; // Total de paquetes

            // Calcular importe total
            $importe = $cantidadPaquetes * $precioPorPaquete; // Total de paquetes * precio por paquete

            // Actualizar el importe en el detalle
            $this->pedido_detalles[$index]['importe'] = $importe;

            // Log para verificar el cálculo
            logger("Cálculo de importe:", [
                "producto_id" => $producto->id,
                "precioCaja" => $precioCaja,
                "cantidadProducto" => $cantidadProducto,
                "cantidadIngresada" => $cantidad,
                "cajas" => $cajas,
                "paquetes" => $paquetes,
                "cantidadPaquetes" => $cantidadPaquetes,
                "precioPorPaquete" => $precioPorPaquete,
                "importeCalculado" => $importe,
            ]);
        }
    }

    public function ajustarCantidad($index)
    {
        $detalle = $this->pedido_detalles[$index];
        $cantidad = $detalle['cantidad'];

        // Separar la cantidad ingresada en cajas y paquetes
        if (strpos($cantidad, '.') !== false) {
            list($cajas, $paquetes) = explode('.', $cantidad);
            $cajas = (int)$cajas; // Convertir a entero
            $paquetes = (int)$paquetes; // Convertir a entero
        } else {
            $cajas = (int)$cantidad;
            $paquetes = 0;
        }

        // Validar que los paquetes no excedan la cantidad de productos por caja
        $producto = Producto::find($detalle['producto_id']);
        $cantidadProducto = $producto->cantidad; // Cantidad de productos por caja

        if ($paquetes >= $cantidadProducto) {
            // Ajustar la cantidad si los paquetes son iguales o mayores que la cantidad de productos por caja
            $cajas += floor($paquetes / $cantidadProducto);
            $paquetes = $paquetes % $cantidadProducto; // Obtener el resto de paquetes
        }

        // Actualizar la cantidad en el detalle
        $this->pedido_detalles[$index]['cantidad'] = $cajas + ($paquetes / 100); // Convertir de nuevo a formato X.Y

        // Recalcular el importe
        $this->calcularImporte($index);
    }

    public function calcularSubtotal()
    {
        return array_sum(array_column($this->pedido_detalles, 'importe'));
    }

    // Método que se ejecuta cuando cambia el vendedor_id
    public function updatedVendedorId($value)
    {
        if ($this->user->hasRole("admin")) {
            $this->cliente_id = null;
            if ($value) {
                $this->dispatch('vendedorSelected', $value);
            }
        }
    }

    public function handleClienteSelected($clienteId)
    {
        if ($clienteId) {
            $this->cliente_id = $clienteId;
            $this->updatedClienteId($clienteId);
        } else {
            $this->resetClienteData();
            $this->cliente_id = null;
        }
    }
}
