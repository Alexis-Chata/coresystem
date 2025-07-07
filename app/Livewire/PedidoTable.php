<?php

namespace App\Livewire;

use App\Models\Almacen;
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
use Illuminate\Support\Facades\Cache;
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
    public array $pedido_detalles = [];
    public $importe_total = 0;
    public $nro_doc_liquidacion;
    public $f_tipo_comprobante_id = "";
    public $tipoComprobantes = [];
    public $comentarios = "";
    public $listado_productos = [];
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
        'recargar-productos' => 'cargarProductos',
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
        $this->cargarProductos();
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

    public function agregarProducto($array_productos)
    {
        // Obtener productos en una sola consulta
        $productos = Producto::withTrashed()
            ->with(['listaPrecios' => fn($q) => $q->where("lista_precio_id", $this->lista_precio)])
            ->whereIn('id', array_column($array_productos, 'id'))
            ->get()
            ->keyBy('id');

        if (!$this->lista_precio) {
            $this->dispatch("error-guardando-pedido", "Error al guardar el pedido" . "<br>" . "No se ha definido una lista de precios. No se puede procesar, vuelva a ingresar el pedido.");
            return;
        }

        foreach ($array_productos as $item) {
            $producto_id = $item['id'];
            $producto = $productos[$item['id']] ?? null;

            if (!$producto) {
                $this->dispatch("error-guardando-pedido", "Error al guardar el pedido" . "<br>" . "El producto con ID {$item['id']} no existe o fue eliminado. Por favor, vuelva a agregarlo.");
                return;
            }

            $existe = collect($this->pedido_detalles)->first(fn($detalle) => $detalle["producto_id"] === $producto_id);
            if ($existe) continue;

            $this->pedido_detalles[] = $this->formatearDetalle($producto, $item['cantidad']);
            $this->calcularImporte(count($this->pedido_detalles) - 1, $producto);
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

    protected function formatearDetalle($producto, $cantidad_ofrecida)
    {
        $cantidad = floatval($cantidad_ofrecida);
        $cantidad = $cantidad > 0 ? number_format_punto2($cantidad) : 0.01;
        $paquetes = convertir_a_paquetes($cantidad, $producto->cantidad);
        $cantidad = convertir_a_cajas($paquetes, $producto->cantidad);

        $detalle = [
            "producto_id" => $producto->id,
            "codigo"      => $producto->id,
            "nombre"      => $producto->name,
            "cantidad"    => $cantidad,
            "importe" => 0, // Se calculará en el siguiente paso
            "marca_id"    => $producto->marca_id,
        ];

        //$detalle['importe'] = $this->calcularImporteIndividual($producto, $cantidad);

        return $detalle;
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

    private function calcularTotal()
    {
        $this->importe_total = collect($this->pedido_detalles)->sum("importe");
    }

    public function guardar_pedido_items($items)
    {
        if (empty($items)) {
            return response()->json(['error' => 'No se enviaron productos.'], 422);
        }

        // Validar todos los ítems antes de hacer nada más
        foreach ($items as $item) {
            if (empty($item['cantidad']) || floatval($item['cantidad']) <= 0) {
                $this->dispatch("error-guardando-pedido", "Error al guardar el pedido" . "<br>" . "El producto {$item['nombre']} tiene una cantidad inválida.");
            }

            if (!isset($item['unidades']) || intval($item['unidades']) <= 0) {
                $this->dispatch("error-guardando-pedido", "Error al guardar el pedido" . "<br>" . "El producto {$item['nombre']} tiene unidades inválidas.");
            }
        }

        $this->pedido_detalles = [];
        $this->agregarProducto($items);

        foreach ($items as $item) {
            $this->cantidad_ofrecida = $item['cantidad'];
        }
        //dd($this->pedido_detalles);
        $this->guardarPedido();
    }


    public function guardarPedido()
    {
        $this->resetValidation();
        $this->validate();

        try {
            DB::beginTransaction();
            Cache::lock('guardar_pedido', 15)->block(10, function () {
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
                    "user_id" => $this->user->id,
                ]);

                foreach ($this->pedido_detalles as $index => $detalle) {

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
            });
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

    public function calcularImporte($index, Producto $producto = null)
    {
        if (!isset($this->pedido_detalles[$index]) || !$this->lista_precio) {
            return;
        }

        $detalle = $this->pedido_detalles[$index];

        $producto = $producto ?? Producto::withTrashed()
            ->with(['listaPrecios' => fn($q) => $q->where("lista_precio_id", $this->lista_precio)])
            ->find($detalle["producto_id"]);

        if (!$producto) {
            $this->pedido_detalles[$index]["importe"] = 0;
            return;
        }

        $precioCaja = $producto->listaPrecios->first()?->pivot->precio ?? 0;
        $cantidadPorCaja = $producto->cantidad;

        if ($cantidadPorCaja <= 0) {
            logger("Cantidad del producto inválida", [
                "producto_id" => $producto->id,
                "cantidad" => $cantidadPorCaja
            ]);
            $this->pedido_detalles[$index]["importe"] = 0;
            return;
        }

        $cantidad = (float) $detalle["cantidad"];
        $cantidadPaquetes = convertir_a_paquetes($cantidad, $cantidadPorCaja);
        $cantidad = convertir_a_cajas($cantidadPaquetes, $cantidadPorCaja);
        list($cajas, $paquetes) = explode('.', $cantidad);

        $precioCaja = $producto->f_tipo_afectacion_id === '21' ? 0 : $precioCaja;

        $importe = number_format_punto2(($cantidadPaquetes * $precioCaja) / $cantidadPorCaja);

        // Guardar referencias
        $this->pedido_detalles[$index] = array_merge(
            $this->pedido_detalles[$index],
            [
                "importe" => $importe,
                "ref_producto_lista_precio" => $this->lista_precio,
                "ref_producto_precio_cajon" => $precioCaja,
                "ref_producto_cantidad_cajon" => $cantidadPorCaja,
                "ref_producto_cant_vendida" => $cantidad,
            ]
        );

        logger("Cálculo de importe:", [
            "producto_id" => $producto->id,
            "precioCaja" => $precioCaja,
            "cantidadProducto" => $cantidadPorCaja,
            "cantidadIngresada" => $cantidad,
            "cajas" => $cajas,
            "paquetes" => $paquetes,
            "cantidadPaquetes" => $cantidadPaquetes,
            "precioPorPaquete" => $precioCaja / $cantidadPorCaja,
            "importeCalculado" => $importe,
        ]);
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

    public function cargarProductos()
    {
        $lista_precio = $this->lista_precio;
        if (!$lista_precio) {
            return;
        }
        $sedes_id = auth_user()->user_empleado->empleado->fSede->empresa->sedes->pluck('id');
        $almacenes = Almacen::whereIn('f_sede_id', $sedes_id)->get();
        $this->listado_productos =  Producto::with([
            'tipoAfectacion',
            "marca:id,name", // optimizamos cargando solo 'nombre'
            "listaPrecios" => function ($query) use ($lista_precio) {
                $query->where("lista_precio_id", $lista_precio)->select('producto_id', 'precio');
            },
            "almacenProductos" => function ($query) use ($almacenes) {
                $query->whereIn("almacen_id", $almacenes->pluck("id"))->select('producto_id', 'stock_disponible');
            },
        ])
            ->get() //;dd($this->listado_productos->first());
            ->map(function ($producto) use ($lista_precio) {
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->name,
                    'factor' => $producto->cantidad,
                    'marca' => $producto->marca->name ?? 'SIN MARCA',
                    'precio' => optional($producto->listaPrecios->first())->precio ?? 0,
                    'stock' => optional($producto->almacenProductos->first())->stock ?? 0,
                    'deleted_at' => $producto->deleted_at,
                    'lista_precio' => $lista_precio,
                    'f_tipo_afectacion_id' => $producto->f_tipo_afectacion_id,
                    'f_tipo_afectacion_name' => $producto->tipoAfectacion->name ?? 'null',
                ];
            })->values()->all();
    }
}
