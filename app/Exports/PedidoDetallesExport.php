<?php

namespace App\Exports;

use App\Models\PedidoDetalle;
use App\Models\ProductoListaPrecio;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PedidoDetallesExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    private $fecha_inicio;
    private $fecha_fin;

    public function __construct($fecha_inicio = null, $fecha_fin = null)
    {
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin = $fecha_fin;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $fechaInicio = $this->fecha_inicio ?? now()->toDateString();
        $fechaFin = $this->fecha_fin ?? now()->toDateString();

        ini_set('memory_limit', '512M');
        $producto_lista_precio = ProductoListaPrecio::withTrashed()->get();
        $pedidoDetalles = DB::table('productos')
            ->join('marcas', 'productos.marca_id', '=', 'marcas.id')
            ->join('pedido_detalles', 'pedido_detalles.producto_id', '=', 'productos.id')
            ->join('pedidos', 'pedido_detalles.pedido_id', '=', 'pedidos.id')
            ->select([
                'pedidos.cliente_id as cliente_cod',
                'pedidos.fecha_emision as pedido_fecha',
                'marcas.name as marca_name',
                'pedido_detalles.*',
            ])
            ->selectRaw('FLOOR(pedido_detalles.cantidad) as bultos')
            ->selectRaw('CAST((pedido_detalles.cantidad - FLOOR(pedido_detalles.cantidad)) * 100 AS UNSIGNED) as unidades')
            ->selectRaw('CAST(ROUND( pedido_detalles.producto_precio * FLOOR(pedido_detalles.cantidad) + ((pedido_detalles.producto_precio * (pedido_detalles.cantidad - FLOOR(pedido_detalles.cantidad)) * 100) / pedido_detalles.producto_cantidad_caja) + 0.0001, 2) AS DECIMAL(10,2)) as importe2')
            ->selectRaw('CASE WHEN CAST(ROUND( pedido_detalles.producto_precio * FLOOR(pedido_detalles.cantidad) + ((pedido_detalles.producto_precio * (pedido_detalles.cantidad - FLOOR(pedido_detalles.cantidad)) * 100) / pedido_detalles.producto_cantidad_caja) + 0.0001, 2) AS DECIMAL(10,2)) = pedido_detalles.importe THEN 1 ELSE 0 END as importe_igual')
            ->whereBetween('pedidos.fecha_emision', [$fechaInicio, $fechaFin])
            ->orderBy('pedido_detalles.id', 'asc')
            ->get();


        return $pedidoDetalles;
    }

    public function headings(): array
    {
        $encabezados = [
            [
                'cliente_cod',
                'pedido_fecha',
                'marca',
                'id',
                'pedido_id',
                'item',
                'producto_id',
                'producto_name',
                'cantidad',
                'producto_precio',
                'producto_cantidad_caja',
                'lista_precio',
                'importe',
                'created_at',
                'updated_at',
                'bultos',
                'unidades',
                'importe2',
                'verificacion',
            ],
        ];

        return $encabezados;
    }
}
