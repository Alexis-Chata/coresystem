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
use App\Traits\StockTrait;
use Exception;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PedidoTable extends Component
{
    use CalculosTrait;
    use StockTrait;
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
    public $importe_total = 0;
    public $nro_doc_liquidacion;
    public $f_tipo_comprobante_id = "";
    public $tipoComprobantes = [];
    public $comentarios = "";
    public $totales = [
        "valorVenta" => 0,
        "totalImpuestos" => 0,
        "subTotal" => 0,
    ];
    public $cantidad_ofrecida = 0.01;

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
        "cliente-selected" => "handleClienteSelected",
    ];

    public function mount()
    {
        $this->user = auth_user();
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
        $this->fecha_emision = Carbon::now()->format("Y-m-d");

        if (!$this->user->can("admin pedido")) {
            $this->vendedor_id = $this->empleado->id;
        }
    }

    private function loadDataByRole()
    {
        if ($this->user->can("admin pedido")) {
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
            $this->tipoComprobantes = FTipoComprobante::where(
                "estado",
                true
            )->get();
        }
    }

    public function updatedClienteId($value)
    {
        // Limpiar los productos del pedido al cambiar de cliente
        $this->pedido_detalles = [];
        $this->importe_total = 0;

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
        $this->f_tipo_comprobante_id = "";
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

        // Si el tipo de documento es RUC, establecer automáticamente Factura
        if ($cliente->tipoDocumento->tipo_documento === "RUC") {
            $facturaComprobante = FTipoComprobante::where(
                "tipo_comprobante",
                "01"
            )->first();
            if ($facturaComprobante) {
                $this->f_tipo_comprobante_id = $facturaComprobante->id;
            }
        }
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
                $keywords = explode(' ', $this->search); // Dividir la búsqueda en palabras clave
                foreach ($keywords as $keyword) {
                    $keyword = trim($keyword);
                    if (!empty($keyword)) {
                        $query->where("name", "like", "%" . $keyword . "%"); // Usar where para cada palabra clave
                    }
                }
                $query->orWhere("id", "like", "%" . $this->search . "%"); // Mantener la búsqueda por ID
            })
                ->with([
                    "marca",
                    "listaPrecios" => function ($query) {
                        $query->where("lista_precio_id", $this->lista_precio);
                    },
                ])
                ->take(15)
                ->get();

            // Debug para verificar los precios
            // logger("Productos encontrados:", [
            //     "lista_precio" => $this->lista_precio,
            //     "productos" => $this->productos->map(function ($producto) {
            //         return [
            //             "id" => $producto->id,
            //             "name" => $producto->name,
            //             "precio" => $producto->listaPrecios->first()?->pivot
            //                 ?->precio,
            //         ];
            //     }),
            // ]);
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

        $cantidad = $producto->cantidad == 1 ? 1 : 0.01; // <-- Nueva lógica
        if ($this->cantidad_ofrecida > 0) {
            $cantidad = number_format_punto2($this->cantidad_ofrecida);
        }

        if (!$existe) {
            // Agregar el producto al detalle
            $this->pedido_detalles[] = [
                "producto_id" => $producto->id,
                "codigo" => $producto->id,
                "nombre" => $producto->name,
                "cantidad" => $cantidad,
                "importe" => 0, // Se calculará en el siguiente paso
                "marca_id" => $producto->marca_id,
            ];

            // Calcular el importe usando el método existente
            $this->calcularImporte(count($this->pedido_detalles) - 1);
        }
        //dd($this->pedido_detalles);
        // Limpiar búsqueda
        $this->search = "";
        $this->productos = [];
        $this->cantidad_ofrecida = 0.01;

        $this->actualizarTotales();

        // Ordenar el detalle por producto_id ascendentemente
        usort($this->pedido_detalles, function ($a, $b) {
            return $a["producto_id"] <=> $b["producto_id"];
        });
    }

    private function actualizarTotales()
    {
        $totalesTemp = $this->setSubTotalesIgv($this->pedido_detalles);
        $this->totales = [
            "valorVenta" => $totalesTemp["valorVenta"],
            "totalImpuestos" => $totalesTemp["totalImpuestos"],
            "subTotal" => $totalesTemp["subTotal"],
            "mtoImpVenta" => $totalesTemp["mtoImpVenta"],
        ];
    }

    public function eliminarDetalle($index)
    {
        unset($this->pedido_detalles[$index]);
        $this->pedido_detalles = array_values($this->pedido_detalles);
        $this->actualizarTotales();
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
        $this->resetValidation();
        $this->validate();

        // foreach ($this->pedido_detalles as $index => $item_detalle) {
        //     $this->actualizarCantidad($index);
        // }

        try {
            DB::beginTransaction();

            $this->importe_total = collect($this->pedido_detalles)->sum(
                "importe"
            );
            if ($this->importe_total <= 0) {
                throw new \Exception("Importe Total no valido<br />");
            }

            $almacen_id = Empleado::with(['fSede.almacen'])->find($this->vendedor_id)->fSede->almacen->id;
            $this->validarStock_arraydetalles($this->pedido_detalles, $almacen_id);
            $this->validarPrecio_arraydetalles($this->pedido_detalles, $almacen_id);

            $pedido = Pedido::create([
                "ruta_id" => $this->ruta_id,
                "f_tipo_comprobante_id" => $this->f_tipo_comprobante_id,
                "vendedor_id" => $this->vendedor_id,
                "cliente_id" => $this->cliente_id,
                "fecha_emision" => $this->fecha_emision,
                "importe_total" => $this->importe_total,
                "nro_doc_liquidacion" => $this->nro_doc_liquidacion,
                "lista_precio" => $this->lista_precio,
                "comentario" => $this->comentarios,
                "empresa_id" => $this->empresa->id,
            ]);

            foreach ($this->pedido_detalles as $index => $detalle) {
                $producto = Producto::find($detalle["producto_id"]);
                $precioCaja =
                    $producto->listaPrecios
                    ->where("id", $this->lista_precio)
                    ->first()->pivot->precio ?? 0;

                PedidoDetalle::create([
                    "pedido_id" => $pedido->id,
                    "item" => $index + 1,
                    "producto_id" => $detalle["producto_id"],
                    "producto_name" => $detalle["nombre"],
                    "cantidad" => $detalle["cantidad"],
                    "producto_precio" => $detalle["ref_producto_precio_cajon"],
                    "producto_cantidad_caja" => $detalle["ref_producto_cantidad_cajon"],
                    "importe" => $detalle["importe"],
                    "lista_precio" => $detalle["ref_producto_lista_precio"],
                ]);
            }

            $this->actualizarStock($pedido);

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
                "comentarios",
            ]);

            // Emitir eventos para limpiar los componentes
            if ($this->user->can("admin pedido")) {
                $this->dispatch("reset-vendedor-select");
            }
            $this->dispatch("reset-cliente-select");

            $this->dispatch("pedido-guardado", "Pedido guardado exitosamente");
        } catch (Exception | LockTimeoutException $e) {
            DB::rollback();
            logger("Error al guardar pedido:", ["error" => $e->getMessage()]);
            //throw $e; // Relanza la excepción si necesitas propagarla
            $this->dispatch("error-guardando-pedido", "Error al guardar el pedido" . "<br>" . $e->getMessage());
            $this->addError("error_guardar", $e->getMessage());
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
        $producto = Producto::find($detalle["producto_id"]);

        if ($producto) {
            $precioCaja =
                $producto->listaPrecios
                ->where("id", $this->lista_precio)
                ->first()->pivot->precio ?? 0;
            $cantidadProducto = $producto->cantidad; // Cantidad de productos por caja

            // Validar que la cantidad del producto no sea cero
            if ($cantidadProducto <= 0) {
                logger(
                    "Error: La cantidad del producto es cero o negativa para el producto:",
                    [
                        "producto_id" => $producto->id,
                        "precioCaja" => $precioCaja,
                        "cantidadProducto" => $cantidadProducto,
                    ]
                );
                $this->pedido_detalles[$index]["importe"] = 0;
                return;
            }

            // Calcular precio por paquete
            $precioPorPaquete = $precioCaja / $cantidadProducto; // 108.00 / 36 = 3.00
            if ($producto->f_tipo_afectacion_id == '21') {
                $precioPorPaquete = 0;
            }

            // Interpretar la cantidad ingresada
            $cantidad = $detalle["cantidad"]; // Cantidad ingresada en cajas y paquetes

            // Separar la cantidad en cajas y paquetes
            $cajas = floor($cantidad); // Parte entera representa las cajas
            $paquetes = round(($cantidad - $cajas) * 100); // Parte decimal convertida a paquetes

            // Validar que los paquetes no excedan la cantidad de productos por caja
            if ($paquetes >= $cantidadProducto) {
                $this->ajustarCantidad($index);
            }

            // Calcular cantidad total de paquetes
            $cantidadPaquetes = $cajas * $cantidadProducto + $paquetes; // Total de paquetes

            // Calcular importe total
            $importe = number_format_punto2($cantidadPaquetes * $precioPorPaquete); // Total de paquetes * precio por paquete

            // Actualizar el importe en el detalle
            $this->pedido_detalles[$index]["importe"] = $importe;
            $this->pedido_detalles[$index]["ref_producto_lista_precio"] = $this->lista_precio;
            $this->pedido_detalles[$index]["ref_producto_precio_cajon"] = $precioCaja;
            $this->pedido_detalles[$index]["ref_producto_cantidad_cajon"] = $cantidadProducto;
            $this->pedido_detalles[$index]["ref_producto_cant_vendida"] = $cantidad;

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
        // Convierte el valor en número
        $cantidad = $this->pedido_detalles[$index]['cantidad'] == "" ? 0 : $this->pedido_detalles[$index]['cantidad'];
        $this->pedido_detalles[$index]['cantidad'] = number_format_punto2($cantidad <= 0 ? 0.01 : $cantidad);

        $detalle = $this->pedido_detalles[$index];
        $cantidad = number_format_punto2($detalle["cantidad"]);

        // Separar la cantidad ingresada en cajas y paquetes
        if (strpos($cantidad, ".") !== false) {
            list($cajas, $paquetes) = explode(".", $cantidad);
            $cajas = $cajas; // Convertir a entero
            $paquetes = str_pad($paquetes, 2, "0"); // Mantener formato de dos dígitos
        } else {
            $cajas = $cantidad;
            $paquetes = "00";
        }

        // Validar que los paquetes no excedan la cantidad de productos por caja
        $producto = Producto::find($detalle["producto_id"]);
        $cantidadProducto = $producto->cantidad; // Cantidad de productos por caja

        if ($paquetes >= $cantidadProducto) {
            // Ajustar la cantidad si los paquetes son iguales o mayores que la cantidad de productos por caja
            $cajas += floor($paquetes / $cantidadProducto);
            $paquetes = str_pad(
                $paquetes % $cantidadProducto,
                2,
                "0",
                STR_PAD_LEFT
            ); // Mantener formato de dos dígitos
        }

        // Actualizar la cantidad en el detalle
        $this->pedido_detalles[$index]["cantidad"] = $cajas . "." . $paquetes;

        // Recalcular el importe
        $this->calcularImporte($index);
        $this->actualizarTotales();
    }

    public function calcularSubtotal()
    {
        return array_sum(array_column($this->pedido_detalles, "importe"));
    }

    // Método que se ejecuta cuando cambia el vendedor_id
    public function updatedVendedorId($value)
    {
        if ($this->user->can("admin pedido")) {
            $this->cliente_id = null;
            if ($value) {
                $this->dispatch("vendedorSelected", $value);
            }
        }
    }

    public function handleClienteSelected($clienteId)
    {
        $this->resetValidation('cliente_id');
        if ($clienteId) {
            $this->cliente_id = $clienteId;
            $this->updatedClienteId($clienteId);
        } else {
            $this->resetClienteData();
            $this->cliente_id = null;
        }
    }
}
