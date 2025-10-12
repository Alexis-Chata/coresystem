<?php

namespace App\Exports;

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

        //ini_set('memory_limit', '512M');
        //$producto_lista_precio = ProductoListaPrecio::withTrashed()->get();
        $pedidoDetalles = DB::table('productos')
            ->join('marcas', 'productos.marca_id', '=', 'marcas.id')
            ->join('pedido_detalles', 'pedido_detalles.producto_id', '=', 'productos.id')
            ->join('pedidos', 'pedido_detalles.pedido_id', '=', 'pedidos.id')
            ->join('empleados', 'pedidos.vendedor_id', '=', 'empleados.id')
            ->join('producto_lista_precios', function ($join) {
                $join->on('producto_lista_precios.producto_id', '=', 'productos.id')
                    ->on('producto_lista_precios.lista_precio_id', '=', 'pedido_detalles.lista_precio');
            })
            ->select([
                'pedidos.cliente_id as cliente_cod',
                'pedidos.conductor_id as conductor_cod',
                'pedidos.fecha_emision as pedido_fecha',
                'pedidos.estado as pedido_estado',
                'marcas.id as marca_id',
                'marcas.name as marca_name',
                'empleados.id as vendedor_id',
                'empleados.name as vendedor_name',
                'pedido_detalles.*',
                'producto_lista_precios.precio as precio_actual', // Agregamos el precio actual
            ])
            ->selectRaw('FLOOR(pedido_detalles.cantidad_unidades / pedido_detalles.producto_cantidad_caja) as bultos')
            ->selectRaw('pedido_detalles.cantidad_unidades % pedido_detalles.producto_cantidad_caja as unidades')
            ->selectRaw('CAST(ROUND((pedido_detalles.producto_precio * pedido_detalles.cantidad_unidades) / pedido_detalles.producto_cantidad_caja, 2) AS DECIMAL(10,2)) as calculando_importe')
            ->selectRaw('ROUND((pedido_detalles.producto_precio * pedido_detalles.cantidad_unidades) / pedido_detalles.producto_cantidad_caja, 2) as verificacion_importe')
            ->selectRaw('CASE WHEN pedido_detalles.producto_precio = producto_lista_precios.precio THEN 1 ELSE 0 END as verificacion_precio') // Nueva comparación de precios
            ->whereBetween('pedidos.fecha_emision', [$fechaInicio, $fechaFin])
            ->havingRaw('verificacion_importe = 0 OR verificacion_precio = 0') // Filtra solo los casos donde hay diferencias
            ->orderBy('pedido_detalles.id', 'asc')
            ->get();

        return $pedidoDetalles;
    }

    public function headings(): array
    {
        $encabezados = [
            [
                'cliente_cod',
                'conductor_cod',
                'pedido_fecha',
                'pedido_estado',
                'marca_id',
                'marca_name',
                'vendedor_id',
                'vendedor_name',
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
                'peso',
                'almacen_producto_id',
                'cantidad_unidades',
                'created_at',
                'updated_at',
                'precio_actual',
                'bultos',
                'unidades',
                'calculando_importe',
                'verificacion_importe',
                'verificacion_precio',
            ],
        ];

        return $encabezados;
    }
}
