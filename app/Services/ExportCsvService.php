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
    public static function exportClientes()
    {
        $clientes = Cliente::with('tipoDocumento')->get();
        $filePath = 'exports/clientes.csv';
        $handle = fopen(storage_path("app/$filePath"), 'w');

        // Encabezados
        fwrite($handle, implode('|', [
            'CódigoProveedor', 'CodigoDistribuidor', 'CodigoCliente', 'NombreCliente', 'TipoDocumento', 'DI',
            'Dirección', 'Mercado', 'Módulo', 'Canal', 'GiroNegocio', 'SubGiroNegocio', 'Ubigeo', 'Distrito',
            'Estatus', 'X', 'Y', 'CodigoPadre', 'FechaIngreso', 'FechaActualización', 'FechaProceso',
            'REF1', 'REF2', 'REF3', 'REF4', 'REF5', 'REF6', 'REF7', 'REF8', 'REF9', 'REF10'
        ]) . PHP_EOL); // Usando el carácter NUL como enclosure

        // Datos
        foreach ($clientes as $cliente) {
            fwrite($handle, implode('|', [
                '', // CódigoProveedor (fijo)
                '', // CodigoDistribuidor (fijo)
                str_pad($cliente->id, 8, '0', STR_PAD_LEFT), // CodigoCliente (rellenado con ceros)
                $cliente->razon_social ?? '', // NombreCliente
                $cliente->tipoDocumento->name ?? '', // TipoDocumento
                $cliente->numero_documento ?? '', // DI
                $cliente->direccion ?? '', // Dirección
                '', '', '', '', '', // Mercado, Módulo, Canal, GiroNegocio, SubGiroNegocio (Opcionales)
                $cliente->ubigeo_inei, // Ubigeo
                '', // Distrito (Opcional)
                'A', // Estatus (Asignamos "A" por defecto)
                '0', // X (Coordenada Longitud)
                '0', // Y (Coordenada Latitud)
                '', // CodigoPadre (Opcional)
                $cliente->created_at->format('Y-m-d'), // FechaIngreso
                $cliente->updated_at->format('Y-m-d'), // FechaActualización
                now()->format('Y-m-d H:i:s'), // FechaProceso
                '', '', '', '', '', '', '', '', '', '' // REF1 - REF10 (Opcionales)
            ]) . PHP_EOL); // Usando el carácter NUL como enclosure
        }

        fclose($handle);
        return $filePath;
    }

    public static function exportProductos()
    {
        $productos = Producto::with('listaPrecios')->get();
        $filePath = 'exports/productos.csv';
        $handle = fopen(storage_path("app/$filePath"), 'w');

        // Encabezados
        fwrite($handle, implode('|', [
            'CódigoProveedor', 'CodigoDistribuidor', 'CodigoProducto', 'NombreProducto', 'EAN', 'DUN',
            'FactorCaja', 'Peso', 'FlagBonificado', 'Afecto', 'PrecioCompra', 'PrecioSugerido', 'PrecioPromedio',
            'FechaProceso', 'REF1', 'REF2', 'REF3', 'REF4', 'REF5', 'REF6', 'REF7', 'REF8', 'REF9', 'REF10'
        ]) . PHP_EOL);

        // Datos
        foreach ($productos as $producto) {
            fwrite($handle, implode('|', [
                '', // CódigoProveedor (asignado por VidaSoftware)
                '', // CodigoDistribuidor (asignado por ARCOR)
                str_pad($producto->id, 8, '0', STR_PAD_LEFT), // CodigoProducto (rellenado con ceros)
                $producto->name, // NombreProducto
                '', // EAN (Si se agrega en el futuro)
                '', // DUN (Si se agrega en el futuro)
                $producto->cantidad, // FactorCaja
                number_format($producto->peso, 4, '.', ''), // Peso con precisión (14,4)
                $producto->tipo === 'bonificado' ? 'B' : 'P', // FlagBonificado
                $producto->afecto_icbper ? '1' : '0', // Afecto a impuestos (1=Afecto, 0=Exonerado)
                optional($producto->listaPrecios->first())->pivot->precio ?? '0.00', // PrecioCompra
                optional($producto->listaPrecios->first())->pivot->precio ?? '0.00', // PrecioSugerido
                optional($producto->listaPrecios->first())->pivot->precio ?? '0.00', // PrecioPromedio
                now()->format('Y-m-d H:i:s'), // FechaProceso
                '', '', '', '', '', '', '', '', '', '' // REF1 - REF10
            ]) . PHP_EOL);
        }

        fclose($handle);
        return $filePath;
    }

    public static function exportStock()
    {
        $stock = AlmacenProducto::with(['producto', 'almacen'])->get();
        $filePath = 'exports/stock.csv';
        $handle = fopen(storage_path("app/$filePath"), 'w');

        // Encabezados
        fwrite($handle, implode('|', [
            'CódigoProveedor', 'CodigoDistribuidor', 'CodigoAlmacen', 'NombreAlmacen', 'CodigoProducto',
            'Lote', 'FechaVencimiento', 'StockEnUnidadMinima', 'UnidadDeMedidaMinima', 'StockEnUnidadesMaximas',
            'UnidadDeMedidaMaxima', 'ValorStock', 'FechaProceso', 'IngresosEnUnidadDeConsumo', 'ValorIngresos',
            'VentasEnUnidadDeConsumo', 'ValorVentas', 'OtrosEnUnidadDeConsumo', 'ValorOtros', 'Periodo',
            'REF1', 'REF2', 'REF3', 'REF4', 'REF5', 'REF6', 'REF7', 'REF8', 'REF9', 'REF10'
        ]) . PHP_EOL);

        // Datos
        foreach ($stock as $item) {
            fwrite($handle, implode('|', [
                '', // CódigoProveedor (asignado por VidaSoftware)
                '', // CodigoDistribuidor (asignado por ARCOR)
                str_pad($item->almacen_id, 8, '0', STR_PAD_LEFT), // CodigoAlmacen (rellenado con ceros)
                $item->almacen->nombre ?? 'Principal', // NombreAlmacen
                str_pad($item->producto_id, 8, '0', STR_PAD_LEFT), // CodigoProducto (rellenado con ceros)
                '', // Lote (Si se agrega en el futuro)
                '', // FechaVencimiento (Si se agrega en el futuro)
                $item->stock_fisico, // StockEnUnidadMinima
                $item->producto->tipo_unidad ?? '', // UnidadDeMedidaMinima
                number_format($item->stock_subcantidad_fisico, 4, '.', ''), // StockEnUnidadesMaximas
                '', // UnidadDeMedidaMaxima (Si se agrega en el futuro)
                '', // ValorStock (Si se necesita calcular)
                now()->format('Y-m-d H:i:s'), // FechaProceso
                '', '', '', '', '', '', // Ingresos, ValorIngresos, Ventas, ValorVentas, Otros, ValorOtros
                now()->format('m'), // Periodo (mes actual)
                '', '', '', '', '', '', '', '', '', '' // REF1 - REF10
            ]) . PHP_EOL);
        }

        fclose($handle);
        return $filePath;
    }

    public static function exportVendedores()
    {
        $vendedores = Empleado::with('tipoDocumento')->where('tipo_empleado', 'vendedor')->get();
        $filePath = 'exports/vendedores.csv';
        $handle = fopen(storage_path("app/$filePath"), 'w');

        // Encabezados
        fwrite($handle, implode('|', [
            'CódigoProveedor', 'CodigoDistribuidor', 'CodigoVendedor', 'NombreVendedor', 'TipoDocumento', 'DI',
            'Canal', 'FechaIngreso', 'FechaActualización', 'FechaProceso', 'Exclusivo',
            'Codigovisor', 'NombreSupervisor', 'REF1', 'REF2', 'REF3', 'REF4', 'REF5', 'REF6', 'REF7', 'REF8', 'REF9', 'REF10'
        ]) . PHP_EOL);

        // Datos
        foreach ($vendedores as $vendedor) {
            fwrite($handle, implode('|', [
                '', // CódigoProveedor (asignado por VidaSoftware)
                '', // CodigoDistribuidor (asignado por ARCOR)
                $vendedor->codigo ?? str_pad($vendedor->id, 8, '0', STR_PAD_LEFT), // CodigoVendedor (rellenado con ceros si es necesario)
                $vendedor->name, // NombreVendedor
                $vendedor->tipoDocumento->name ?? '', // TipoDocumento
                $vendedor->numero_documento ?? '', // DI
                '', // Canal (Si se agrega en el futuro)
                $vendedor->created_at->format('Y-m-d'), // FechaIngreso
                $vendedor->updated_at->format('Y-m-d'), // FechaActualización
                now()->format('Y-m-d H:i:s'), // FechaProceso
                '0', // Exclusivo (Asignamos "0" por defecto)
                '', // Codigovisor (Si se agrega en el futuro)
                '', // NombreSupervisor (Si se agrega en el futuro)
                '', '', '', '', '', '', '', '', '', '' // REF1 - REF10
            ]) . PHP_EOL);
        }

        fclose($handle);
        return $filePath;
    }

    public static function exportVentas()
    {
        $ventas = FComprobanteSunat::with(['detalle', 'cliente', 'vendedor', 'ruta'])->whereBetween('fechaEmision', [
            now()->subMonths(1)->startOfMonth(),
            now()->endOfMonth()
        ])->get();

        $filePath = 'exports/ventas.csv';
        $handle = fopen(storage_path("app/$filePath"), 'w');

        // Encabezados
        fwrite($handle, implode('|', [
            'CódigoProveedor', 'CodigoDistribuidor', 'TipoDocumento', 'NroDocumento', 'FechaDocumento', 'MotivoNC',
            'Origen', 'CodigoCliente', 'CanalCliente', 'TipoNegocio', 'CodigoVendedor', 'CanalVendedor', 'Ruta',
            'NumeroItem', 'CodigoProducto', 'CantidadUnidadMinima', 'TipoUnidadMinima', 'CantidadUnidadMaxima',
            'TipoUnidadMaxima', 'Moneda', 'ImporteNetoSinImpuesto', 'ImporteNetoConImpuesto', 'Descuento',
            'TipoVenta', 'CodCombo', 'CodPromoción', 'TipoDocumentoReferencia', 'NroDocumentoReferencia',
            'FechaDocumentoReferencia', 'FechaProceso', 'REF1', 'REF2', 'REF3', 'REF4', 'REF5', 'REF6', 'REF7',
            'REF8', 'REF9', 'REF10'
        ]) . PHP_EOL);

        // Datos
        foreach ($ventas as $venta) {
            foreach ($venta->detalle as $index => $detalle) {
                fwrite($handle, implode('|', [
                    '', // CódigoProveedor (asignado por VidaSoftware)
                    '', // CodigoDistribuidor (asignado por ARCOR)
                    $venta->tipoDoc, // TipoDocumento (FA, BO, NC, ND)
                    "{$venta->serie}-{$venta->correlativo}", // NroDocumento
                    $venta->fechaEmision->format('Y-m-d'), // FechaDocumento
                    $venta->desMotivo ?? '', // MotivoNC (Si aplica)
                    '', // Origen (Si se agrega en el futuro)
                    str_pad($venta->cliente_id, 25, '0', STR_PAD_LEFT), // CodigoCliente
                    '', // CanalCliente (Si se agrega en el futuro)
                    '', // TipoNegocio (Si se agrega en el futuro)
                    str_pad($venta->vendedor_id, 25, '0', STR_PAD_LEFT), // CodigoVendedor
                    '', // CanalVendedor (Si se agrega en el futuro)
                    str_pad($venta->ruta_id, 10, '0', STR_PAD_LEFT), // Ruta
                    $index + 1, // NumeroItem
                    str_pad($detalle->producto_id, 25, '0', STR_PAD_LEFT), // CodigoProducto
                    $venta->tipoDoc === 'NC' ? -$detalle->cantidad : $detalle->cantidad, // CantidadUnidadMinima (Negativo si NC)
                    $detalle->unidad_medida ?? '', // TipoUnidadMinima
                    '', // CantidadUnidadMaxima (Si se agrega en el futuro)
                    '', // TipoUnidadMaxima (Si se agrega en el futuro)
                    $venta->tipoMoneda, // Moneda
                    number_format($detalle->valor_venta, 4, '.', ''), // ImporteNetoSinImpuesto
                    number_format($detalle->mtoImpVenta, 4, '.', ''), // ImporteNetoConImpuesto
                    number_format($detalle->descuento ?? 0, 4, '.', ''), // Descuento
                    'P', // TipoVenta (Asumimos "P", si es bonificación cambiar a "B")
                    '', // CodCombo (Si se agrega en el futuro)
                    '', // CodPromoción (Si se agrega en el futuro)
                    $venta->tipDocAfectado ?? '', // TipoDocumentoReferencia
                    $venta->numDocfectado ?? '', // NroDocumentoReferencia
                    '', // FechaDocumentoReferencia (Si se agrega en el futuro)
                    now()->format('Y-m-d H:i:s'), // FechaProceso
                    '', '', '', '', '', '', '', '', '', '' // REF1 - REF10
                ]) . PHP_EOL);
            }
        }

        fclose($handle);
        return $filePath;
    }

    public static function exportRutas()
    {
        $rutas = Ruta::with(['vendedor', 'clientes'])->get();
        $filePath = 'exports/rutas.csv';
        $handle = fopen(storage_path("app/$filePath"), 'w');

        // Encabezados
        fwrite($handle, implode('|', [
            'CódigoProveedor', 'CodigoDistribuidor', 'CodigoCliente', 'CodigoVendedor', 'FuerzaDeVenta',
            'FrecuenciaVisita', 'Zona', 'Mesa', 'Ruta', 'Modulo', 'FechaProceso',
            'REF1', 'REF2', 'REF3', 'REF4', 'REF5', 'REF6', 'REF7', 'REF8', 'REF9', 'REF10'
        ]) . PHP_EOL);

        // Datos
        foreach ($rutas as $ruta) {
            foreach ($ruta->clientes as $cliente) {
                fwrite($handle, implode('|', [
                    '', // CódigoProveedor (asignado por VidaSoftware)
                    '', // CodigoDistribuidor (asignado por ARCOR)
                    str_pad($cliente->id, 8, '0', STR_PAD_LEFT), // CodigoCliente
                    str_pad($ruta->vendedor_id, 8, '0', STR_PAD_LEFT), // CodigoVendedor
                    '', // FuerzaDeVenta (Si se agrega en el futuro)
                    self::convertirFrecuenciaVisita($ruta->dia_visita), // FrecuenciaVisita
                    '', // Zona (Si se agrega en el futuro)
                    '', // Mesa (Si se agrega en el futuro)
                    str_pad($ruta->codigo, 8, '0', STR_PAD_LEFT), // Ruta
                    '', // Modulo (Si se agrega en el futuro)
                    now()->format('Y-m-d H:i:s'), // FechaProceso
                    '', '', '', '', '', '', '', '', '', '' // REF1 - REF10
                ]) . PHP_EOL);
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

    public static function exportPedidos()
    {
        $pedidos = Pedido::with(['pedidoDetalles', 'cliente', 'vendedor', 'tipoComprobante'])
            ->whereBetween('fecha_emision', [
                now()->subMonths(1)->startOfMonth(),
                now()->endOfMonth()
            ])->get();

        $filePath = 'exports/pedidos.csv';
        $handle = fopen(storage_path("app/$filePath"), 'w');

        // Encabezados
        fwrite($handle, implode('|', [
            'CódigoProveedor', 'CodigoDistribuidor', 'CodigoCliente', 'CodigoVendedor', 'Origen', 'CodigoPedido',
            'FechaPedido', 'EstatusPedido', 'MotivoCancelación', 'TipoDocumento', 'Documento', 'FechaDocumento',
            'EstatusDocumento', 'NumeroItem', 'CodigoProducto', 'TipoProducto', 'CodPromoción', 'CantidadUnidadMinima',
            'TipoUnidadMinima', 'CantidadUnidadMaxima', 'TipoUnidadMaxima', 'ImportePedidoNetoSinImpuesto',
            'ImportePedidoNetoConImpuesto', 'Descuento', 'FechaProceso', 'REF1', 'REF2', 'REF3', 'REF4', 'REF5',
            'REF6', 'REF7', 'REF8', 'REF9', 'REF10'
        ]) . PHP_EOL);

        // Datos
        foreach ($pedidos as $pedido) {
            foreach ($pedido->pedidoDetalles as $index => $detalle) {
                fwrite($handle, implode('|', [
                    '', // CódigoProveedor (asignado por VidaSoftware)
                    '', // CodigoDistribuidor (asignado por ARCOR)
                    str_pad($pedido->cliente_id, 8, '0', STR_PAD_LEFT), // CodigoCliente
                    str_pad($pedido->vendedor_id, 8, '0', STR_PAD_LEFT), // CodigoVendedor
                    '', // Origen (Si se agrega en el futuro)
                    str_pad($pedido->id, 8, '0', STR_PAD_LEFT), // CodigoPedido
                    $pedido->fecha_emision->format('Y-m-d'), // FechaPedido
                    self::convertirEstatusPedido($pedido->estado), // EstatusPedido
                    '', // MotivoCancelación (Si se agrega en el futuro)
                    $pedido->tipoComprobante->codigo ?? '', // TipoDocumento
                    '', // Documento (Si se agrega en el futuro)
                    $pedido->fecha_reparto ? $pedido->fecha_reparto->format('Y-m-d') : '', // FechaDocumento
                    '', // EstatusDocumento (Si se agrega en el futuro)
                    $index + 1, // NumeroItem
                    str_pad($detalle->producto_id, 8, '0', STR_PAD_LEFT), // CodigoProducto
                    'P', // TipoProducto (Asumimos "P", si es bonificación cambiar a "B")
                    '', // CodPromoción (Si se agrega en el futuro)
                    $detalle->cantidad, // CantidadUnidadMinima
                    $detalle->unidad_medida ?? '', // TipoUnidadMinima
                    '', // CantidadUnidadMaxima (Si se agrega en el futuro)
                    '', // TipoUnidadMaxima (Si se agrega en el futuro)
                    number_format($detalle->valor_venta, 4, '.', ''), // ImportePedidoNetoSinImpuesto
                    number_format($detalle->mtoImpVenta, 4, '.', ''), // ImportePedidoNetoConImpuesto
                    number_format($detalle->descuento ?? 0, 4, '.', ''), // Descuento
                    now()->format('Y-m-d H:i:s'), // FechaProceso
                    '', '', '', '', '', '', '', '', '', '' // REF1 - REF10
                ]) . PHP_EOL);
            }
        }

        fclose($handle);
        return $filePath;
    }

    private static function convertirEstatusPedido($estado)
    {
        return match ($estado) {
            'facturado' => 'APRO',
            'pendiente', 'movimiento-generado', 'asignado' => 'PEND',
            default => 'CANC',
        };
    }

}
