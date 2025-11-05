<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

    public function consulta_comprobantes()
    {
        //
    }
}
