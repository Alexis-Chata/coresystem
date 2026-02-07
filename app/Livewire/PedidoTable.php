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
use Illuminate\Support\Str;

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
    public string $pedido_uuid;
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
        $this->pedido_uuid = (string) Str::uuid();

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
        $this->tipoComprobantes = FTipoComprobante::where(
            "estado",
            true
        )->get();
    }

    public function updatedClienteId($value)
    {
        // Limpiar los productos del pedido al cambiar de cliente
        $this->pedido_detalles = [];
        $this->importe_total = 0;

        // Al cambiar cliente, dejamos vacío para que la regla aplique default (boleta) o fuerce factura
        $this->f_tipo_comprobante_id = "";

        if (!$value) {
            $this->resetClienteData();
            $this->loadTipoComprobantes(); // opcional: volver a todos
            return;
        }

        $cliente = Cliente::with(["ruta", "listaPrecio", "tipoDocumento"])->find($value);

        // Cargar opciones permitidas en el combo según el cliente
        $this->loadTipoComprobantesPorCliente($cliente);

        if ($cliente) {
            $this->updateClienteData($cliente);
            $this->aplicarReglaComprobante($cliente);
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

        $this->cargarProductos();
    }

    private function loadTipoComprobantesPorCliente(?Cliente $cliente): void
    {
        $codigoDoc = $cliente?->tipoDocumento?->codigo ?? null;

        $permitidos = ($codigoDoc == 6)
            ? ['01']         // RUC => solo Factura
            : ['03', '00'];  // No RUC => Boleta o Nota

        $this->tipoComprobantes = FTipoComprobante::where('estado', true)
            ->whereIn('tipo_comprobante', $permitidos)
            ->get();
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
            //             "precio" => $producto->listaPrecios->first()?->pivot?->precio,
            //         ];
            //     }),
            // ]);
        } else {
            $this->productos = [];
        }
    }

    public function agregarProducto($array_productos)
    {
        if (!$this->lista_precio) {
            logger("Error PedidoTable: lista_precio no definida", ["lista_precio" => $this->lista_precio, "cliente_id"   => $this->cliente_id,]);
            $cliente = Cliente::find($this->cliente_id);
            if ($cliente) {
                $this->lista_precio = $cliente->lista_precio_id;
            } else {
                logger("Error PedidoTable: cliente no encontrado", ["cliente_id" => $this->cliente_id]);
            }
        }
        if (!$this->lista_precio) {
            $this->dispatch("error-guardando-pedido", "Error al guardar el pedido" . "<br>" . "No se ha definido una lista de precios. No se puede procesar, vuelva a ingresar el pedido.");
            return;
        }
        $almacen_id = Empleado::with(['fSede.almacen'])->find($this->vendedor_id)->fSede->almacen->id;
        // Obtener productos en una sola consulta
        $productos = Producto::withTrashed()
            ->with([
                'listaPrecios' => fn($q) => $q->where("lista_precio_id", $this->lista_precio),
                'almacenProductos' => fn($q) => $q->where("almacen_id", $almacen_id),
            ])
            ->whereIn('id', array_column($array_productos, 'id'))
            ->get()
            ->keyBy('id');

        foreach ($array_productos as $item) {
            $producto_id = $item['id'];
            $producto = $productos[$item['id']] ?? null;

            if (!$producto) {
                $this->dispatch("error-guardando-pedido", "Error al guardar el pedido" . "<br>" . "El producto con ID {$item['id']} no existe o fue eliminado. Por favor, vuelva a agregarlo.");
                return;
            }
            if (!$producto->almacenProductos->first()) {
                $this->dispatch("error-guardando-pedido", "Error al guardar el pedido" . "<br>" . "El producto ({$item['id']} - {$producto->name}) aún no ha sido ingresado en almacén. Sin Stock Disponible.");
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
        $cantidad = $cantidad > 0 ? number_format($cantidad, calcular_digitos($producto->cantidad), '.', '') : 0.01;
        $paquetes = convertir_a_paquetes($cantidad, $producto->cantidad);
        $cantidad = convertir_a_cajas($paquetes, $producto->cantidad);

        $detalle = [
            "producto_id" => $producto->id,
            "codigo"      => $producto->id,
            "nombre"      => $producto->name,
            "cantidad"    => $cantidad,
            "peso"        => number_format((($producto->peso * $paquetes) / $producto->cantidad), 3, '.', ''),
            "importe"     => 0, // Se calculará en el siguiente paso
            "marca_id"    => $producto->marca_id,
            "almacen_producto_id" => $producto->almacenProductos->first()->id,
            "cantidad_unidades"    => $paquetes,
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
                $this->dispatch("error-guardando-pedido", "Error al guardar el pedido<br>El producto {$item['nombre']} tiene una cantidad inválida.");
                return;
            }

            if (!isset($item['unidades']) || intval($item['unidades']) <= 0) {
                $this->dispatch("error-guardando-pedido", "Error al guardar el pedido<br>El producto {$item['nombre']} tiene unidades inválidas.");
                return;
            }
        }

        $this->pedido_detalles = [];

        $cliente = Cliente::with(['tipoDocumento'])->find($this->cliente_id);

        if ($cliente) {
            $this->lista_precio = $cliente->lista_precio_id;
        }

        $this->aplicarReglaComprobante($cliente);

        $this->agregarProducto($items);

        // dd($items, $this->pedido_detalles);
        $this->guardarPedido();
    }

    private function aplicarReglaComprobante(?Cliente $cliente): void
    {
        $facturaId = FTipoComprobante::where('estado', true)->where('tipo_comprobante', '01')->value('id');
        $boletaId  = FTipoComprobante::where('estado', true)->where('tipo_comprobante', '03')->value('id');
        $notaId    = FTipoComprobante::where('estado', true)->where('tipo_comprobante', '00')->value('id');

        $codigoDoc = $cliente?->tipoDocumento?->codigo ?? null;

        // RUC => siempre Factura
        if ($codigoDoc == 6) {
            $this->f_tipo_comprobante_id = $facturaId ? (string)$facturaId : "";
            return;
        }

        // No RUC => permitir Boleta o Nota; si no eligió o eligió inválido => Boleta
        $permitidos = array_filter([(string)$boletaId, (string)$notaId]);
        $actual = (string)($this->f_tipo_comprobante_id ?? "");

        if ($actual === "" || !in_array($actual, $permitidos, true)) {
            $this->f_tipo_comprobante_id = $boletaId ? (string)$boletaId : "";
        }
    }

    public function guardarPedido()
    {
        $this->resetValidation();
        $this->validate();

        $lock = Cache::lock('guardar_pedido', 15);

        try {
            // 🔒 Espera automática (como compra online)
            $lock->block(10);

            $wasCreated = false;

            $pedido = DB::transaction(function () use (&$wasCreated) {

                $this->importe_total = collect($this->pedido_detalles)->sum("importe");
                if ($this->importe_total <= 0) {
                    throw new \Exception("Importe Total no válido");
                }

                $almacen_id = Empleado::with(['fSede.almacen'])->find($this->vendedor_id)->fSede->almacen->id;
                $this->validarStock_arraydetalles($this->pedido_detalles, $almacen_id);
                $this->validarPrecio_arraydetalles($this->pedido_detalles, $almacen_id);

                // ✅ Idempotencia por UUID
                $pedido = Pedido::firstOrCreate(
                    ["uuid" => $this->pedido_uuid],
                    [
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
                    ]
                );

                $wasCreated = $pedido->wasRecentlyCreated;

                // IMPORTANTÍSIMO: solo crear detalles y mover stock si el pedido fue NUEVO
                if ($wasCreated) {
                    foreach ($this->pedido_detalles as $index => $detalle) {

                        PedidoDetalle::create([
                            "pedido_id" => $pedido->id,
                            "item" => $index + 1,
                            "producto_id" => $detalle["producto_id"],
                            "producto_name" => $detalle["nombre"],
                            "cantidad" => $detalle["cantidad"],
                            "peso" => $detalle["peso"],
                            "producto_precio" => $detalle["ref_producto_precio_cajon"],
                            "producto_cantidad_caja" => $detalle["ref_producto_cantidad_cajon"],
                            "importe" => $detalle["importe"],
                            "lista_precio" => $detalle["ref_producto_lista_precio"],
                            "almacen_producto_id" => $detalle["almacen_producto_id"],
                            "cantidad_unidades" => $detalle["cantidad_unidades"],
                        ]);
                    }

                    $this->actualizarStock($pedido);

                    $subTotalesIgv = $this->setSubTotalesIgv($this->pedido_detalles);

                    // Actualizar el pedido con el total final (por si acaso)
                    $pedido->update([
                        "importe_total" => $this->importe_total,
                    ]);
                }

                return $pedido;
            });

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
            $this->pedido_uuid = (string) Str::uuid();
            // ✅ Mensaje final
            $this->dispatch(
                'pedido-guardado',
                $wasCreated
                    ? 'Pedido guardado exitosamente'
                    : 'Este pedido ya fue procesado'
            );
        } catch (LockTimeoutException $e) {
            $this->dispatch('error-guardando-pedido', 'El sistema está muy ocupado, intente nuevamente');
        } catch (\Exception $e) {
            logger("Error al guardar pedido:", ["error" => $e->getMessage()]);
            //throw $e; // Relanza la excepción si necesitas propagarla
            $this->dispatch("error-guardando-pedido", "Error al guardar el pedido<br>" . $e->getMessage());
            $this->addError("error_guardar", $e->getMessage());
        } finally {
            optional($lock)->release();
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

        $precioImporte = $producto->f_tipo_afectacion_id == '21' ? 0 : $precioCaja;

        $importe = number_format_punto2(($cantidadPaquetes * $precioImporte) / $cantidadPorCaja);
        $peso = number_format((($producto->peso * $cantidadPaquetes) / $cantidadPorCaja), 3, '.', '');
        // Guardar referencias
        $this->pedido_detalles[$index] = array_merge(
            $this->pedido_detalles[$index],
            [
                "peso" => $peso,
                "importe" => $importe,
                "ref_producto_lista_precio" => $this->lista_precio,
                "ref_producto_precio_cajon" => $precioCaja,
                "ref_producto_cantidad_cajon" => $cantidadPorCaja,
                "ref_producto_cant_vendida" => $cantidad,
            ]
        );

        // logger("Cálculo de importe:", [
        //     "producto_id" => $producto->id,
        //     "precioCaja" => $precioCaja,
        //     "cantidadProducto" => $cantidadPorCaja,
        //     "cantidadIngresada" => $cantidad,
        //     "cajas" => $cajas,
        //     "paquetes" => $paquetes,
        //     "cantidadPaquetes" => $cantidadPaquetes,
        //     "precioPorPaquete" => $precioCaja / $cantidadPorCaja,
        //     "importeCalculado" => $importe,
        //     "peso" => $peso,
        // ]);
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
        $lista_precio_id = $this->lista_precio;
        if (! $lista_precio_id) {
            $this->listado_productos = [];
            return;
        }
        $sedes_id = auth_user()->user_empleado->empleado->fSede->empresa->sedes->pluck('id');
        $almacenesIds = Almacen::whereIn('f_sede_id', $sedes_id)->pluck('id');

        $productos = Producto::query()
            // ✅ SOLO productos que están activos en ESTA lista de precios
            ->whereHas('listaPrecios', function ($q) use ($lista_precio_id) {
                $q->where('lista_precios.id', $lista_precio_id)
                    ->where('producto_lista_precios.activo', 1);
            })
            ->with([
                'tipoAfectacion',
                'marca:id,name',
                // ✅ Traer SOLO el pivot de esta lista y activo
                'listaPrecios' => function ($q) use ($lista_precio_id) {
                    $q->where('lista_precios.id', $lista_precio_id)
                        ->where('producto_lista_precios.activo', 1);
                },
                'almacenProductos' => function ($query) use ($almacenesIds) {
                    $query->whereIn('almacen_id', $almacenesIds)->select('producto_id', 'almacen_id', 'stock_disponible');
                },
            ])
            ->get(); //;dd($this->listado_productos->first());

        $this->listado_productos = $productos->map(function ($producto) use ($lista_precio_id) {
            $pivot = $producto->listaPrecios->first()?->pivot;

            // Si tienes varios almacenes, usualmente conviene sumar:
            $stockTotal = $producto->almacenProductos->sum('stock_disponible');

            return [
                'id' => $producto->id,
                'nombre' => $producto->name,
                'factor' => $producto->cantidad,
                'marca' => $producto->marca->name ?? 'SIN MARCA',
                'precio' => $pivot?->precio ?? 0,
                'stock' => $stockTotal,
                'deleted_at' => $producto->deleted_at, // sigue siendo borrado global si lo usas
                'lista_precio' => $lista_precio_id,
                'activo_lista' => (bool) ($pivot?->activo ?? false),
                'f_tipo_afectacion_id' => $producto->f_tipo_afectacion_id,
                'f_tipo_afectacion_name' => $producto->tipoAfectacion->name ?? null,
            ];
        })->values()->all();
    }
}
