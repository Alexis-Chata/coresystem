<?php

namespace App\Traits;

use App\Models\Producto;
use Luecano\NumeroALetras\NumeroALetras;

trait CalculosTrait
{

    public function setSubTotalesIgv($detalles)
    {
        $data_detalles = $detalles;
        foreach ($data_detalles as $key => $data_detalle) {
            $producto = Producto::find($data_detalle['producto_id']);

            $data_detalle['tipAfeIgv'] = $producto->f_tipo_afectacion_id;
            $data_detalle['mtoPrecioUnitario'] = $data_detalle['importe'] / $data_detalle['cantidad'];
            $data_detalle['porcentajeIgv'] = $producto->porcentaje_igv ?? 0;
            $data_detalle['porcentajeIsc'] = $producto->porcentaje_isc ?? 0;
            $data_detalle['factorIcbper'] = $producto->porcentaje_icbper ?? 0;
            $diferencia = ($data_detalle['mtoPrecioUnitario'] - $data_detalle['factorIcbper']);
            $porcentaje = ($data_detalle['porcentajeIgv'] + $data_detalle['porcentajeIsc']);
            $data_detalle['mtoValorUnitario'] = number_format(($diferencia) / (1 + ($porcentaje / 100)), 4, '.', '');
            $data_detalle['mtoValorVenta'] = $data_detalle['mtoValorUnitario'] * $data_detalle['cantidad'];

            $data_detalle['mtoBaseIgv'] = $data_detalle['mtoValorVenta'] ?? 0;
            $data_detalle['mtoBaseIsc'] = ($data_detalle['porcentajeIsc'] > 0) ? $data_detalle['mtoValorVenta'] : 0;
            $data_detalle['igv'] = $data_detalle['importe'] - $data_detalle['mtoBaseIgv'];
            $data_detalle['isc'] = number_format($data_detalle['mtoBaseIgv'] * ($data_detalle['porcentajeIsc'] / 100), 4, '.', ''); //
            $data_detalle['icbper'] = number_format($data_detalle['factorIcbper'] * $data_detalle['cantidad'], 4, '.', '');
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
        $data['subTotal'] = $data['valorVenta'] + $data['totalImpuestos']; // total

        $data['mtoImpVenta'] = floor($data['subTotal'] * 10) / 10; // total con redondeo

        $data['redondeo'] = $data['mtoImpVenta'] - $data['subTotal'];
        // dd($details, $data);
        return $data;
    }

    public function setLegends(&$data)
    {
        $formatter = new NumeroALetras();

        $data['legends'] = [
            [
                'code' => '1000',
                'value' => $formatter->toInvoice($data['mtoImpVenta'], 2, 'SOLES')
            ]
        ];
    }
}
