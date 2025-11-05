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
            ->when($tipos, fn ($q) => $q->whereIn('f_tipo_comprobante_id', $tipos))
            ->when($sede_id, fn ($q) => $q->where('f_sede_id', $sede_id))
            ->get();

        return response()->json($series);
    }

    public function lista_comprobantes(Request $request)
    {
        $sede_id = $request->query('sede_id');
        $serie = $request->query('serie');
        $desde = (int) $request->query('desde');
        $hasta = (int) $request->query('hasta');

        $comprobantes = FComprobanteSunat::with([
            'vendedor',
            'tipo_doc',
            'cliente.padron' => function ($query) {
                $query->withTrashed();
            },
            'conductor',
            'detalle.producto',
        ])
            ->where('sede_id', $sede_id)
            ->where('serie', $serie)
            ->whereRaw('CAST(correlativo AS UNSIGNED) BETWEEN ? AND ?', [$desde, $hasta])
            ->orderByRaw('CAST(correlativo AS UNSIGNED) ASC')
            ->get();

        return response()->json($comprobantes);
    }
}
