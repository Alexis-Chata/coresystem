<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FComprobanteSunat;
use App\Models\FSerie;
use Illuminate\Http\Request;

class ImpresionController extends Controller
{
    public function lista_series(Request $request)
    {
        $tipos = explode(',', $request->query('tipos', ''));
        $sede_id = $request->query('sede_id');

        $series = FSerie::with(['fSede', 'fTipoComprobante'])
            ->when($tipos, fn($q) => $q->whereIn('f_tipo_comprobante_id', $tipos))
            ->when($sede_id, fn($q) => $q->where('f_sede_id', $sede_id))
            ->get();

        return response()->json($series);
    }

    public function lista_comprobantes(Request $request)
    {
        $sede_id = $request->query('sede_id');
        $serie = $request->query('serie');
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        $comprobantes = FComprobanteSunat::with([
            'vendedor',
            'tipo_doc',
            'cliente.padron',
            'conductor',
            'detalle.producto'
        ])
            ->where('sede_id', $sede_id)
            ->where('serie', $serie)
            ->whereBetween('correlativo', [$desde, $hasta])
            ->get();

        // Transformación opcional para mantener campos exactos como en tu método imprimir()
        $data = $comprobantes->map(function ($c) {
            return [
                'id' => $c->id,
                'sede_id' => $c->sede_id,
                'serie' => $c->serie,
                'correlativo' => $c->correlativo,
                'tipoDoc' => $c->tipoDoc,
                'tipoDoc_name' => $c->tipo_doc->nombre ?? '',
                'fechaEmision' => $c->fechaEmision,
                'companyRazonSocial' => $c->companyRazonSocial,
                'companyRuc' => $c->companyRuc,
                'companyAddressDireccion' => $c->companyAddressDireccion,
                'cliente_id' => $c->cliente_id,
                'clientNumDoc' => $c->clientNumDoc,
                'clientRazonSocial' => $c->clientRazonSocial,
                'clientDireccion' => $c->clientDireccion,
                'vendedor_id' => $c->vendedor_id,
                'vendedor' => $c->vendedor,
                'conductor_id' => $c->conductor_id,
                'conductor' => $c->conductor,
                'ruta_id' => $c->ruta_id,
                'detalle' => $c->detalle->map(function ($d) {
                    return [
                        'codProducto' => $d->codProducto,
                        'descripcion' => $d->descripcion,
                        'ref_producto_cantidad_cajon' => $d->ref_producto_cantidad_cajon,
                        'ref_producto_cant_vendida' => $d->ref_producto_cant_vendida,
                        'ref_producto_precio_cajon' => $d->ref_producto_precio_cajon,
                        'mtoValorVenta' => $d->mtoValorVenta,
                        'mtoValorUnitario' => $d->mtoValorUnitario,
                        'tipAfeIgv' => $d->tipAfeIgv,
                        'totalImpuestos' => $d->totalImpuestos,
                        'producto' => $d->producto,
                    ];
                }),
                'subTotal' => $c->subTotal,
                'valorVenta' => $c->valorVenta,
                'totalImpuestos' => $c->totalImpuestos,
                'mtoImpVenta' => $c->mtoImpVenta,
                'deleted_at' => $c->deleted_at,
            ];
        });

        return response()->json($data);
    }
}
