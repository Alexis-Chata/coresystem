<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Producto;
use App\Models\AlmacenProducto;
use App\Models\Empleado;
use App\Models\FComprobanteSunat;
use App\Models\Pedido;
use App\Models\Ruta;
use Illuminate\Support\Facades\Storage;

class ExportCsvService
{
    public static $encabezados = true;

    public static function exportClientes($marcaId, $exportDir = 'exports')
    {
        // Definimos valores por defecto según la marca
        $codigoProveedor = match ((int) $marcaId) {
            7 => "10005",
            10 => "10004",
            default => "",
        };
        $codigoDistribuidor = match ((int) $marcaId) {
            7 => "LIMA.01.10732237",
            10 => "58204545",
            default => "",
        };

        $clientes = Cliente::with(['tipoDocumento', 'listaPrecio'])
            // ->whereHas('pedidos.pedidoDetalles.producto', function ($query) use ($marcaId) {
            //     $query->where('marca_id', $marcaId);
            // })
            ->whereHas('FComprobanteSunats.detalle.producto', function ($query) use ($marcaId) {
                $query->where('marca_id', $marcaId);
            })
            ->get();
        $filePath = "{$exportDir}/clientes.csv";
        $handle = fopen(storage_path("app/{$filePath}"), 'w');

        // Encabezados
        if (self::$encabezados){
        fwrite($handle, implode('|', [
            'CódigoProveedor', 'CodigoDistribuidor', 'CodigoCliente', 'NombreCliente', 'TipoDocumento', 'DI',
            'Dirección', 'Mercado', 'Módulo', 'Canal', 'GiroNegocio', 'SubGiroNegocio', 'Ubigeo', 'Distrito',
            'Estatus', 'X', 'Y', 'CodigoPadre', 'FechaIngreso', 'FechaActualización', 'FechaProceso',
            'REF1', 'REF2', 'REF3', 'REF4', 'REF5', 'REF6', 'REF7', 'REF8', 'REF9', 'REF10'
        ]) . PHP_EOL); // Usando el carácter NUL como enclosure
        }

        $fechaProceso = now()->format('Y-m-d H:i:s'); // FechaProceso

        // Datos
        foreach ($clientes as $cliente) {
            // Determinar el canal del cliente basado en su lista de precios
            $canal = match ($cliente->lista_precio_id) {
                1 => 'Minorista',
                2 => 'Mayorista',
                default => 'N/D',
            };

            fwrite($handle, implode('|', [
                $codigoProveedor, // CódigoProveedor (fijo)
                $codigoDistribuidor, // CodigoDistribuidor (fijo)
                str_pad($cliente->id, 8, '0', STR_PAD_LEFT), // CodigoCliente (rellenado con ceros)
                $cliente->razon_social ?? '', // NombreCliente
                $cliente->tipoDocumento->tipo_documento ?? '', // TipoDocumento
                $cliente->numero_documento ?? '', // DI
                $cliente->direccion ?? '', // Dirección
                '', '', $canal, '', '', // Mercado, Módulo, Canal, GiroNegocio, SubGiroNegocio (Opcionales)
                '', // Ubigeo
                '', // Distrito (Opcional)
                'A', // Estatus (Asignamos "A" por defecto)
                '0', // X (Coordenada Longitud)
                '0', // Y (Coordenada Latitud)
                '', // CodigoPadre (Opcional)
                $cliente->created_at->format('Y-m-d'), // FechaIngreso
                $cliente->updated_at->format('Y-m-d'), // FechaActualización
                $fechaProceso, // FechaProceso
                '', '', '', '', '', '', '', '', '', '' // REF1 - REF10 (Opcionales)
            ]) . PHP_EOL); // Usando el carácter NUL como enclosure
        }

        fclose($handle);
        return $filePath;
    }

    public static function exportProductos($marcaId, $exportDir = 'exports')
    {
        // Definimos valores por defecto según la marca
        $codigoProveedor = match ((int) $marcaId) {
            7 => "10005",
            10 => "10004",
            default => "",
        };
        $codigoDistribuidor = match ((int) $marcaId) {
            7 => "LIMA.01.10732237",
            10 => "58204545",
            default => "",
        };

        $productos = Producto::where('marca_id', $marcaId)->get();
        $filePath = "{$exportDir}/productos.csv";
        $handle = fopen(storage_path("app/$filePath"), 'w');

        // Encabezados
        if (self::$encabezados){
        fwrite($handle, implode('|', [
            'CódigoProveedor', 'CodigoDistribuidor', 'CodigoProducto', 'NombreProducto', 'EAN', 'DUN',
            'FactorCaja', 'Peso', 'FlagBonificado', 'Afecto', 'PrecioCompra', 'PrecioSugerido', 'PrecioPromedio',
            'FechaProceso', 'REF1', 'REF2', 'REF3', 'REF4', 'REF5', 'REF6', 'REF7', 'REF8', 'REF9', 'REF10'
        ]) . PHP_EOL);
        }

        $fechaProceso = now()->format('Y-m-d H:i:s'); // FechaProceso

        // Datos
        foreach ($productos as $producto) {
            fwrite($handle, implode('|', [
                $codigoProveedor, // CódigoProveedor (asignado por VidaSoftware)
                $codigoDistribuidor, // CodigoDistribuidor (asignado por ARCOR)
                str_pad($producto->id, 8, '0', STR_PAD_LEFT), // CodigoProducto (rellenado con ceros)
                $producto->name, // NombreProducto
                '', // EAN (Si se agrega en el futuro)
                '', // DUN (Si se agrega en el futuro)
                $producto->cantidad, // FactorCaja
                number_format($producto->peso, 4, '.', ''), // Peso con precisión (14,4)
                $producto->f_tipo_afectacion_id == '21' ? 'B' : 'P', // FlagBonificado
                $producto->f_tipo_afectacion_id == '21' ? '0' : '1', // Afecto a impuestos (1=Afecto, 0=Exonerado)
                '0.00', // PrecioCompra
                '0.00', // PrecioSugerido
                '0.00', // PrecioPromedio
                $fechaProceso, // FechaProceso
                '', '', '', '', '', '', '', '', '', '' // REF1 - REF10
            ]) . PHP_EOL);
        }

        fclose($handle);
        return $filePath;
    }

    public static function exportStock($marcaId, $exportDir = 'exports')
    {
        // Definimos valores por defecto según la marca
        $codigoProveedor = match ((int) $marcaId) {
            7 => "10005",
            10 => "10004",
            default => "",
        };
        $codigoDistribuidor = match ((int) $marcaId) {
            7 => "LIMA.01.10732237",
            10 => "58204545",
            default => "",
        };

        $stock = AlmacenProducto::with(['producto'])->whereHas('producto', function ($query) use ($marcaId) {
            $query->where('marca_id', $marcaId);
        })->get();
        $filePath = "{$exportDir}/stock.csv";
        $handle = fopen(storage_path("app/$filePath"), 'w');

        // Encabezados
        if (self::$encabezados){
        fwrite($handle, implode('|', [
            'CódigoProveedor', 'CodigoDistribuidor', 'CodigoAlmacen', 'NombreAlmacen', 'CodigoProducto',
            'Lote', 'FechaVencimiento', 'StockEnUnidadMinima', 'UnidadDeMedidaMinima', 'StockEnUnidadesMaximas',
            'UnidadDeMedidaMaxima', 'ValorStock', 'FechaProceso', 'IngresosEnUnidadDeConsumo', 'ValorIngresos',
            'VentasEnUnidadDeConsumo', 'ValorVentas', 'OtrosEnUnidadDeConsumo', 'ValorOtros', 'Periodo',
            'REF1', 'REF2', 'REF3', 'REF4', 'REF5', 'REF6', 'REF7', 'REF8', 'REF9', 'REF10'
        ]) . PHP_EOL);
        }

        $fechaProceso = now()->format('Y-m-d H:i:s'); // FechaProceso

        // Datos
        foreach ($stock as $item) {
            fwrite($handle, implode('|', [
                $codigoProveedor, // CódigoProveedor (asignado por VidaSoftware)
                $codigoDistribuidor, // CodigoDistribuidor (asignado por ARCOR)
                str_pad($item->almacen_id, 8, '0', STR_PAD_LEFT), // CodigoAlmacen (rellenado con ceros)
                $item->almacen->name ?? '', // NombreAlmacen
                str_pad($item->producto_id, 8, '0', STR_PAD_LEFT), // CodigoProducto (rellenado con ceros)
                'N/D', // Lote (Si se agrega en el futuro)
                '1900-01-01', // FechaVencimiento (Si se agrega en el futuro)
                convertir_a_paquetes($item->stock_fisico, $item->producto->cantidad), // StockEnUnidadMinima
                'Unidad', // UnidadDeMedidaMinima
                number_format($item->stock_fisico, 4, '.', ''), // StockEnUnidadesMaximas
                'Caja', // UnidadDeMedidaMaxima (Si se agrega en el futuro)
                '0', // ValorStock (Si se necesita calcular)
                $fechaProceso, // FechaProceso
                '0', '0', '0', '0', '0', '0', // Ingresos, ValorIngresos, Ventas, ValorVentas, Otros, ValorOtros
                now()->format('m'), // Periodo (mes actual)
                '', '', '', '', '', '', '', '', '', '' // REF1 - REF10
            ]) . PHP_EOL);
        }

        fclose($handle);
        return $filePath;
    }

    public static function exportVendedores($marcaId, $exportDir = 'exports')
    {
        // Definimos valores por defecto según la marca
        $codigoProveedor = match ((int) $marcaId) {
            7 => "10005",
            10 => "10004",
            default => "",
        };
        $codigoDistribuidor = match ((int) $marcaId) {
            7 => "LIMA.01.10732237",
            10 => "58204545",
            default => "",
        };

        $vendedores = Empleado::with(['tipoDocumento', 'rutas'])
            ->where('tipo_empleado', 'vendedor')
            // ->whereHas('pedidos.pedidoDetalles.producto', function ($query) use ($marcaId) {
            //     $query->where('marca_id', $marcaId);
            // })
            ->whereHas('FComprobanteSunats.detalle.producto', function ($query) use ($marcaId) {
                $query->where('marca_id', $marcaId);
            })
            ->get();
        $filePath = "{$exportDir}/vendedores.csv";
        $handle = fopen(storage_path("app/$filePath"), 'w');

        $fechaProceso = now()->format('Y-m-d H:i:s');

        // Encabezados
        if (self::$encabezados){
        fwrite($handle, implode('|', [
            'CódigoProveedor', 'CodigoDistribuidor', 'CodigoVendedor', 'NombreVendedor', 'TipoDocumento', 'DI',
            'Canal', 'FechaIngreso', 'FechaActualización', 'FechaProceso', 'Exclusivo',
            'Codigovisor', 'NombreSupervisor', 'REF1', 'REF2', 'REF3', 'REF4', 'REF5', 'REF6', 'REF7', 'REF8', 'REF9', 'REF10'
        ]) . PHP_EOL);
    }

        // Datos
        foreach ($vendedores as $vendedor) {
            $listaPrecios = $vendedor->rutas->pluck('lista_precio_id')->countBy();
            // Inicializar el canal como 'N/D'
            $canal = 'N/D';
            if ($listaPrecios->isNotEmpty()) {
                // Obtener el valor más frecuente
                $masFrecuente = $listaPrecios->sortDesc()->keys()->first();
                $maxCount = $listaPrecios->max();

                // Verificar si hay empate
                $empate = $listaPrecios->filter(function ($count) use ($maxCount) {
                    return $count == $maxCount;
                })->count() > 1;

                if ($empate) {
                    // Si hay empate, asignar '¿?'
                    $canal = 'Minorista';
                } else {
                    // Asignar el canal según el valor más frecuente
                    $canal = match ($masFrecuente) {
                        1 => 'Minorista',
                        2 => 'Mayorista',
                        default => 'N/D',
                    };
                }
            }
            fwrite($handle, implode('|', [
                $codigoProveedor, // CódigoProveedor (asignado por VidaSoftware)
                $codigoDistribuidor, // CodigoDistribuidor (asignado por ARCOR)
                $vendedor->codigo ?? str_pad($vendedor->id, 8, '0', STR_PAD_LEFT), // CodigoVendedor (rellenado con ceros si es necesario)
                $vendedor->name, // NombreVendedor
                $vendedor->tipoDocumento->tipo_documento ?? '', // TipoDocumento
                $vendedor->numero_documento ?? '', // DI
                $canal, // Canal (Mayorista o Minorista según análisis de rutas)
                $vendedor->created_at->format('Y-m-d'), // FechaIngreso
                $vendedor->updated_at->format('Y-m-d'), // FechaActualización
                $fechaProceso, // FechaProceso
                '0', // Exclusivo (Asignamos "0" por defecto)
                'N/D', // Codigovisor (Si se agrega en el futuro)
                'N/D', // NombreSupervisor (Si se agrega en el futuro)
                '', '', '', '', '', '', '', '', '', '' // REF1 - REF10
            ]) . PHP_EOL);
        }

        fclose($handle);
        return $filePath;
    }

    public static function exportVentas($marcaId, $exportDir = 'exports')
    {
        // Definimos valores por defecto según la marca
        $codigoProveedor = match ((int) $marcaId) {
            7 => "10005",
            10 => "10004",
            default => "",
        };
        $codigoDistribuidor = match ((int) $marcaId) {
            7 => "LIMA.01.10732237",
            10 => "58204545",
            default => "",
        };

        $vendedores = Empleado::withTrashed()->with('rutas')->where('tipo_empleado', 'vendedor')->get()->map(function ($vendedor) {
            $listaPrecios = $vendedor->rutas->pluck('lista_precio_id')->countBy();

            // Inicializar el canal como 'N/D'
            $canal = 'N/D';

            if ($listaPrecios->isNotEmpty()) {
                $maxCount = $listaPrecios->max();
                $masFrecuente = $listaPrecios->filter(fn($count) => $count == $maxCount)->keys();

                // Verificar si hay empate
                if ($masFrecuente->count() > 1) {
                    $canal = 'Minorista'; // Si hay empate, asignar 'Minorista'
                } else {
                    $canal = match ($masFrecuente->first()) {
                        1 => 'Minorista',
                        2 => 'Mayorista',
                        default => 'N/D',
                    };
                }
            }

            // Asignamos el canal al vendedor dentro del mapeo
            $vendedor->canal = $canal;
            return $vendedor;
        })->keyBy('id');

        $ventas = FComprobanteSunat::with([
            'detalle' => function ($query) use ($marcaId) {
                $query->whereHas('producto', function ($q) use ($marcaId) {
                    $q->where('marca_id', $marcaId);
                });
            },
            'cliente',
            'vendedor',
            'ruta',
            'pedido' => function ($query) {
                $query->select('id', 'lista_precio'); // Asegura que se incluya solo el campo necesario
            }
        ])
        ->whereHas('detalle.producto', function ($query) use ($marcaId) {
            $query->where('marca_id', $marcaId);
        })
        ->whereBetween('fechaEmision', [
            now()->subMonths(1)->startOfMonth(),
            now()->endOfMonth()
        ])
        ->where('estado_reporte', true)
        ->get();

        $filePath = "{$exportDir}/ventas.csv";
        $handle = fopen(storage_path("app/$filePath"), 'w');

        // Encabezados
        if (self::$encabezados){
        fwrite($handle, implode('|', [
            'CódigoProveedor', 'CodigoDistribuidor', 'TipoDocumento', 'NroDocumento', 'FechaDocumento', 'MotivoNC',
            'Origen', 'CodigoCliente', 'CanalCliente', 'TipoNegocio', 'CodigoVendedor', 'CanalVendedor', 'Ruta',
            'NumeroItem', 'CodigoProducto', 'CantidadUnidadMinima', 'TipoUnidadMinima', 'CantidadUnidadMaxima',
            'TipoUnidadMaxima', 'Moneda', 'ImporteNetoSinImpuesto', 'ImporteNetoConImpuesto', 'Descuento',
            'TipoVenta', 'CodCombo', 'CodPromoción', 'TipoDocumentoReferencia', 'NroDocumentoReferencia',
            'FechaDocumentoReferencia', 'FechaProceso', 'REF1', 'REF2', 'REF3', 'REF4', 'REF5', 'REF6', 'REF7',
            'REF8', 'REF9', 'REF10'
        ]) . PHP_EOL);
        }

        $fechaProceso = now()->format('Y-m-d H:i:s'); // FechaProceso

        // Datos
        foreach ($ventas as $venta) {
            foreach ($venta->detalle as $index => $detalle) {
                $tipoDoc = match ($venta->tipoDoc) {
                    '01' => 'FA',
                    '03' => 'BO',
                    '07' => 'NC',
                    '08' => 'ND',
                    default => 'BO'
                };

                $canal_cliente = match ((int) $venta->pedido->lista_precio) {
                    1 => 'Minorista',
                    2 => 'Mayorista',
                    default => 'N/D',
                };

                fwrite($handle, implode('|', [
                    $codigoProveedor, // CódigoProveedor (asignado por VidaSoftware)
                    $codigoDistribuidor, // CodigoDistribuidor (asignado por ARCOR)
                    $tipoDoc, // TipoDocumento (FA, BO, NC, ND)
                    "{$venta->serie}-{$venta->correlativo}", // NroDocumento
                    carbon_parse($venta->fechaEmision)->format('Y-m-d'), // FechaDocumento
                    '', // MotivoNC (Si aplica)
                    '', // Origen (Si se agrega en el futuro)
                    str_pad($venta->cliente_id, 8, '0', STR_PAD_LEFT), // CodigoCliente
                    $canal_cliente, // CanalCliente (Si se agrega en el futuro)
                    '', // TipoNegocio (Si se agrega en el futuro)
                    str_pad($venta->vendedor_id, 8, '0', STR_PAD_LEFT), // CodigoVendedor
                    $vendedores[$venta->vendedor_id]->canal, // CanalVendedor (Si se agrega en el futuro)
                    str_pad($venta->ruta_id, 8, '0', STR_PAD_LEFT), // Ruta
                    $index + 1, // NumeroItem
                    str_pad($detalle->codProducto, 8, '0', STR_PAD_LEFT), // CodigoProducto
                    $detalle->cantidad, // CantidadUnidadMinima (Negativo si NC)
                    'Unidad', // TipoUnidadMinima
                    convertir_a_cajas($detalle->cantidad, $detalle->ref_producto_cantidad_cajon), // CantidadUnidadMaxima (Si se agrega en el futuro)
                    'Caja', // TipoUnidadMaxima (Si se agrega en el futuro) CALCULAR
                    $venta->tipoMoneda, // Moneda
                    $detalle->tipAfeIgv == '10' ? number_format(($detalle->mtoPrecioUnitario * $detalle->cantidad) - $detalle->totalImpuestos, 4, '.', '') : number_format(0, 4, '.', ''), // ImporteNetoSinImpuesto
                    $detalle->tipAfeIgv == '10' ? number_format(($detalle->mtoPrecioUnitario * $detalle->cantidad), 4, '.', '') : number_format(0, 4, '.', ''), // ImporteNetoConImpuesto
                    '0', // Descuento
                    $detalle->tipAfeIgv == '10' ? 'P' : 'B',
                    '', // CodCombo (Si se agrega en el futuro)
                    '', // CodPromoción (Si se agrega en el futuro)
                    $venta->tipDocAfectado ?? '', // TipoDocumentoReferencia
                    $venta->numDocfectado ?? '', // NroDocumentoReferencia
                    '', // FechaDocumentoReferencia (Si se agrega en el futuro)
                    $fechaProceso, // FechaProceso
                    '', '', '', '', '', '', '', '', '', '' // REF1 - REF10
                ]) . PHP_EOL);
            }
        }

        fclose($handle);
        return $filePath;
    }

    public static function exportRutas($marcaId, $exportDir = 'exports')
    {
        // Definimos valores por defecto según la marca
        $codigoProveedor = match ((int) $marcaId) {
            7 => "10005",
            10 => "10004",
            default => "",
        };
        $codigoDistribuidor = match ((int) $marcaId) {
            7 => "LIMA.01.10732237",
            10 => "58204545",
            default => "",
        };

        // Primero obtenemos todos los clientes válidos que tienen pedidos con productos de la marca especificada
        $clientesValidos = Cliente::
        // whereHas('pedidos.pedidoDetalles.producto', function ($query) use ($marcaId) {
        //     $query->where('marca_id', $marcaId);
        // })
        whereHas('FComprobanteSunats.detalle.producto', function ($query) use ($marcaId) {
            $query->where('marca_id', $marcaId);
        })
        ->pluck('id')->toArray();

        $rutas = Ruta::with(['vendedor', 'clientes' => function($query) use ($clientesValidos) {
            // Filtramos para incluir solo los clientes que están en la lista de clientes válidos
            $query->whereIn('id', $clientesValidos);
        }])
        // ->whereHas('clientes.pedidos.pedidoDetalles.producto', function ($query) use ($marcaId) {
        //     $query->where('marca_id', $marcaId);
        // })
        ->whereHas('clientes.FComprobanteSunats.detalle.producto', function ($query) use ($marcaId) {
            $query->where('marca_id', $marcaId);
        })
        ->get();

        $filePath = "{$exportDir}/rutas.csv";
        $handle = fopen(storage_path("app/$filePath"), 'w');

        // Encabezados
        if (self::$encabezados){
        fwrite($handle, implode('|', [
            'CódigoProveedor', 'CodigoDistribuidor', 'CodigoCliente', 'CodigoVendedor', 'FuerzaDeVenta',
            'FrecuenciaVisita', 'Zona', 'Mesa', 'Ruta', 'Modulo', 'FechaProceso',
            'REF1', 'REF2', 'REF3', 'REF4', 'REF5', 'REF6', 'REF7', 'REF8', 'REF9', 'REF10'
        ]) . PHP_EOL);
        }

        $fechaProceso = now()->format('Y-m-d H:i:s');

        // Datos
        foreach ($rutas as $ruta) {
            // Solo procesamos rutas que tienen clientes válidos
            if ($ruta->clientes->isNotEmpty()) {
                $canal_ruta = match ((int) $ruta->lista_precio_id) {
                    1 => 'Minorista',
                    2 => 'Mayorista',
                    default => 'N/D',
                };
                foreach ($ruta->clientes as $cliente) {
                    fwrite($handle, implode('|', [
                        $codigoProveedor, // CódigoProveedor (asignado por VidaSoftware)
                        $codigoDistribuidor, // CodigoDistribuidor (asignado por ARCOR)
                        str_pad($cliente->id, 8, '0', STR_PAD_LEFT), // CodigoCliente
                        str_pad($ruta->vendedor_id, 8, '0', STR_PAD_LEFT), // CodigoVendedor
                        $canal_ruta, // FuerzaDeVenta (Si se agrega en el futuro)
                        self::convertirFrecuenciaVisita($ruta->dia_visita ?? 'Lunes'), // FrecuenciaVisita
                        '', // Zona (Si se agrega en el futuro)
                        '', // Mesa (Si se agrega en el futuro)
                        str_pad($ruta->id, 8, '0', STR_PAD_LEFT), // Ruta
                        '', // Modulo (Si se agrega en el futuro)
                        $fechaProceso, // FechaProceso
                        '', '', '', '', '', '', '', '', '', '' // REF1 - REF10
                    ]) . PHP_EOL);
                }
            }
        }

        fclose($handle);
        return $filePath;
    }

    private static function convertirFrecuenciaVisita($dia_visita)
    {
        $dias = ['L' => 0, 'Ma' => 1, 'Mi' => 2, 'J' => 3, 'V' => 4, 'S' => 5, 'D' => 6];
        $frecuencia = 'S'; // Por defecto asumimos semanal
        $diasFlag = '0000000';

        if (str_contains($dia_visita, 'quincenal impar')) {
            $frecuencia = 'K';
        } elseif (str_contains($dia_visita, 'quincenal par')) {
            $frecuencia = 'Q';
        }

        foreach ($dias as $dia => $pos) {
            if (str_contains($dia_visita, $dia)) {
                $diasFlag[$pos] = '1';
            }
        }

        return $frecuencia . $diasFlag;
    }

    public static function exportPedidos($marcaId, $exportDir = 'exports')
    {
        // Definimos valores por defecto según la marca
        $codigoProveedor = match ((int) $marcaId) {
            7 => "10005",
            10 => "10004",
            default => "",
        };
        $codigoDistribuidor = match ((int) $marcaId) {
            7 => "LIMA.01.10732237",
            10 => "58204545",
            default => "",
        };

        // Primero obtenemos todos los productos válidos de la marca especificada
        $productosValidos = Producto::where('marca_id', $marcaId)->pluck('id')->toArray();

        $pedidos = Pedido::with(['pedidoDetalles' => function($query) use ($productosValidos) {
            // Filtramos para incluir solo los productos que están en la lista de productos válidos
            $query->whereIn('producto_id', $productosValidos);
        }, 'cliente', 'vendedor', 'tipoComprobante'])
            ->whereHas('pedidoDetalles.producto', function ($query) use ($marcaId) {
                $query->where('marca_id', $marcaId);
            })
            ->where('estado', 'facturado')
            ->whereBetween('fecha_emision', [
                now()->subMonths(1)->startOfMonth(),
                now()->endOfMonth()
            ])->get();

        $filePath = "{$exportDir}/pedidos.csv";
        $handle = fopen(storage_path("app/$filePath"), 'w');

        // Encabezados
        if (self::$encabezados){
        fwrite($handle, implode('|', [
            'CódigoProveedor', 'CodigoDistribuidor', 'CodigoCliente', 'CodigoVendedor', 'Origen', 'CodigoPedido',
            'FechaPedido', 'EstatusPedido', 'MotivoCancelación', 'TipoDocumento', 'Documento', 'FechaDocumento',
            'EstatusDocumento', 'NumeroItem', 'CodigoProducto', 'TipoProducto', 'CodPromoción', 'CantidadUnidadMinima',
            'TipoUnidadMinima', 'CantidadUnidadMaxima', 'TipoUnidadMaxima', 'ImportePedidoNetoSinImpuesto',
            'ImportePedidoNetoConImpuesto', 'Descuento', 'FechaProceso', 'REF1', 'REF2', 'REF3', 'REF4', 'REF5',
            'REF6', 'REF7', 'REF8', 'REF9', 'REF10'
        ]) . PHP_EOL);
        }

        // Datos
        foreach ($pedidos as $pedido) {
            // Solo procesamos pedidos que tienen detalles válidos
            if ($pedido->pedidoDetalles->isNotEmpty()) {
                foreach ($pedido->pedidoDetalles as $index => $detalle) {
                    $tipoDoc = match ($pedido->tipoDoc) {
                        '01' => 'FA',
                        '03' => 'BO',
                        '07' => 'NC',
                        '08' => 'ND',
                        default => 'BO'
                    };
                    fwrite($handle, implode('|', [
                        $codigoProveedor, // CódigoProveedor (asignado por VidaSoftware)
                        $codigoDistribuidor, // CodigoDistribuidor (asignado por ARCOR)
                        str_pad($pedido->cliente_id, 8, '0', STR_PAD_LEFT), // CodigoCliente
                        str_pad($pedido->vendedor_id, 8, '0', STR_PAD_LEFT), // CodigoVendedor
                        'Toma Pedidos', // Origen (Si se agrega en el futuro)
                        str_pad($pedido->id, 8, '0', STR_PAD_LEFT), // CodigoPedido
                        carbon_parse($pedido->fecha_emision)->format('Y-m-d'), // FechaPedido
                        'APRO', // EstatusPedido
                        '', // MotivoCancelación (Si se agrega en el futuro)
                        $tipoDoc, // TipoDocumento
                        $pedido->id, // Documento (Si se agrega en el futuro)
                        $pedido->fecha_reparto ? $pedido->fecha_reparto->format('Y-m-d') : '', // FechaDocumento
                        'APRO', // EstatusDocumento (Si se agrega en el futuro)
                        $index + 1, // NumeroItem
                        str_pad($detalle->producto_id, 8, '0', STR_PAD_LEFT), // CodigoProducto
                        $detalle->tipAfeIgv == '10' ? 'P' : 'B', // TipoProducto
                        '', // CodPromoción (Si se agrega en el futuro)
                        convertir_a_paquetes($detalle->cantidad, $detalle->producto_cantidad_caja), // CantidadUnidadMaxima (Si se agrega en el futuro), // CantidadUnidadMinima CALCULAR
                        'Unidad', // TipoUnidadMinima
                        $detalle->cantidad, // CantidadUnidadMaxima (Si se agrega en el futuro)
                        'Caja', // TipoUnidadMaxima (Si se agrega en el futuro)
                        number_format($detalle->valor_venta, 4, '.', ''), // ImportePedidoNetoSinImpuesto
                        number_format($detalle->mtoImpVenta, 4, '.', ''), // ImportePedidoNetoConImpuesto
                        number_format($detalle->descuento ?? 0, 4, '.', ''), // Descuento
                        now()->format('Y-m-d H:i:s'), // FechaProceso
                        '', '', '', '', '', '', '', '', '', '' // REF1 - REF10
                    ]) . PHP_EOL);
                }
            }
        }

        fclose($handle);
        return $filePath;
    }
}
