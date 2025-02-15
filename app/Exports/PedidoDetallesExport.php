<?php

namespace App\Exports;

use App\Models\PedidoDetalle;
use App\Models\ProductoListaPrecio;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;

class PedidoDetallesExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $producto_lista_precio = ProductoListaPrecio::withTrashed()->get();
        $pedidoDetalles = PedidoDetalle::with([
            'pedido:id,cliente_id',
            'producto.marca:id,name'
        ])
        ->orderBy('id', 'asc')
        ->get()
        ->map(function ($detalle) {
            return [
                'cliente_cod' => $detalle->pedido->cliente_id,
                'marca_name' => $detalle->producto->marca->name,
                'detalle' => $detalle
            ];
        });
        return $pedidoDetalles;
    }
}
