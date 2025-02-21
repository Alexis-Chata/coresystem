<?php

namespace App\Traits;

use App\Models\Producto;
use Illuminate\Support\Facades\Log;
use Luecano\NumeroALetras\NumeroALetras;

trait CalculosTrait
{

    public function setSubTotalesIgv($detalles, bool $devolver_data_detalle = false)
    {
        $data_detalles = $detalles;
        // dd($data_detalles);
        foreach ($data_detalles as $key => $data_detalle) {
            $producto = Producto::withTrashed()->find($data_detalle['producto_id']);
            $data_detalle['ref_producto_lista_precio'] = $data_detalle['ref_producto_lista_precio'] ?? $data_detalle['lista_precio'];
            $data_detalle['ref_producto_precio_cajon'] = number_format_punto2($data_detalle['ref_producto_precio_cajon'] ?? $data_detalle['producto_precio']);
            $data_detalle['ref_producto_cantidad_cajon'] = $data_detalle['ref_producto_cantidad_cajon'] ?? $data_detalle['producto_cantidad_caja'];
            $data_detalle['ref_producto_cant_vendida'] = number_format_punto2($data_detalle['ref_producto_cant_vendida'] ?? $data_detalle['cantidad']);
            list($data_detalle['bultos'], $data_detalle['unidades']) = explode('.', number_format_punto2($data_detalle['ref_producto_cant_vendida']));
            $data_detalle['cantidad'] = ($data_detalle['bultos'] * $data_detalle['ref_producto_cantidad_cajon']) + $data_detalle['unidades'];

            if ($data_detalle['bultos'] == 0 and $data_detalle['unidades'] == 0) {
                logger("data detalle", ["revisar cantidad vendida" => $data_detalle]);
            }

            $data_detalle['codProducto'] = $data_detalle['producto_id'];
            $data_detalle['unidad'] = "NIU";
            $data_detalle['descripcion'] = $data_detalle['producto_name'] ?? $data_detalle['nombre'];
            $data_detalle['cantidad'] = number_format_punto2($data_detalle['cantidad']);
            $data_detalle['tipAfeIgv'] = $data_detalle['tipAfeIgv'] ?? $producto->f_tipo_afectacion_id;
            $data_detalle['mtoPrecioUnitario'] = $data_detalle['importe'] / $data_detalle['cantidad'];
            $data_detalle['porcentajeIgv'] = $producto->porcentaje_igv ?? 0;
            $data_detalle['porcentajeIsc'] = $producto->porcentaje_isc ?? 0;
            $data_detalle['factorIcbper'] = $producto->porcentaje_icbper ?? 0;
            $diferencia = ($data_detalle['mtoPrecioUnitario'] - $data_detalle['factorIcbper']);
            $porcentaje = ($data_detalle['porcentajeIgv'] + $data_detalle['porcentajeIsc']);
            $data_detalle['mtoValorUnitario'] = (($diferencia) / (1 + ($porcentaje / 100)));
            $data_detalle['mtoValorVenta'] = number_format_punto2($data_detalle['mtoValorUnitario'] * $data_detalle['cantidad']);

            if ($data_detalle['tipAfeIgv'] == 21) {
                $data_detalle['mtoValorGratuito'] = $data_detalle['ref_producto_precio_cajon'] / $data_detalle['ref_producto_cantidad_cajon'];
                $data_detalle['mtoValorVenta'] = $data_detalle['mtoValorGratuito'] * $data_detalle['cantidad'];
            }
            $data_detalle['mtoBaseIgv'] = number_format_punto2($data_detalle['mtoValorVenta'] ?? 0);
            $data_detalle['mtoBaseIsc'] = ($data_detalle['porcentajeIsc'] > 0) ? $data_detalle['mtoValorVenta'] : 0;
            //$data_detalle['igv'] = number_format_punto2(($data_detalle['mtoBaseIgv'] * $data_detalle['porcentajeIgv']) / 100);
            $data_detalle['igv'] = number_format_punto2($data_detalle['importe'] - $data_detalle['mtoBaseIgv']);
            if ($data_detalle['tipAfeIgv'] == 21) {
                $data_detalle['igv'] = 0;
            }
            $data_detalle['isc'] = number_format($data_detalle['mtoBaseIgv'] * ($data_detalle['porcentajeIsc'] / 100), 2, '.', ''); //
            $data_detalle['icbper'] = number_format($data_detalle['factorIcbper'] * $data_detalle['cantidad'], 2, '.', '');
            $data_detalle['totalImpuestos'] = $data_detalle['igv'] + $data_detalle['icbper'] + $data_detalle['isc'];
            $data_detalle['tipSisIsc'] = $data_detalle['tipSisIsc'] ?? "01";
            $data_detalles[$key] = $data_detalle;
        }
        // dd($data_detalles);
        $details = collect($data_detalles);

        $data['mtoOperGravadas'] = $details->where('tipAfeIgv', 10)->sum('mtoValorVenta');
        $data['mtoOperExoneradas'] = $details->where('tipAfeIgv', 20)->sum('mtoValorVenta');
        $data['mtoOperInafectas'] = $details->where('tipAfeIgv', 30)->sum('mtoValorVenta');
        $data['mtoOperExportacion'] = $details->where('tipAfeIgv', 40)->sum('mtoValorVenta');
        $data['mtoOperGratuitas'] = $details->whereNotIn('tipAfeIgv', [10, 20, 30, 40])->sum('mtoValorVenta');

        $data['mtoIGV'] = $details->whereIn('tipAfeIgv', [10, 20, 30, 40])->sum('igv');
        $data['mtoIGVGratuitas'] = $details->whereNotIn('tipAfeIgv', [10, 20, 30, 40])->sum('igv');
        $data['icbper'] = $details->sum('icbper');
        $data['totalImpuestos'] = $data['mtoIGV'] + $data['icbper']; // total de impuestos (igv + icbper)

        $data['valorVenta'] = $details->whereIn('tipAfeIgv', [10, 20, 30, 40])->sum('mtoValorVenta'); //  subtotal
        $data['subTotal'] = number_format_punto2($data['valorVenta'] + $data['totalImpuestos']); // total

        //$data['mtoImpVenta'] = number_format_punto2(floor($data['subTotal'] * 10) / 10); // total con redondeo
        $data['mtoImpVenta'] = number_format_punto2($data['subTotal']); // total sin redondeo

        $data['redondeo'] = number_format_punto2($data['mtoImpVenta'] - $data['subTotal']);
        // dd($details, $data);
        if ($devolver_data_detalle) {
            return [$data, $data_detalles];
        }
        return $data;
    }

    // public function setLegends(&$data)
    // {
    //     $formatter = new NumeroALetras();

    //     $data['legends'] = [
    //         [
    //             'code' => '1000',
    //             'value' => $formatter->toInvoice($data['mtoImpVenta'], 2, 'SOLES')
    //         ]
    //     ];
    // }
}
