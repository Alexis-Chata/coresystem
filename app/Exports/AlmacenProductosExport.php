<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AlmacenProductosExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $almacen_productos = DB::table('almacen_productos')
        ->join('productos', 'almacen_productos.producto_id', '=', 'productos.id')
        ->join('marcas', 'productos.marca_id', '=', 'marcas.id')
        ->select(
            'almacen_productos.almacen_id',
            'marcas.id as marca_id',
            'marcas.name as marca_nombre',
            'almacen_productos.producto_id',
            'productos.name as producto_nombre',
            'productos.deleted_at',
            'productos.cantidad as factor',
            'productos.peso',
            'productos.tipo',
            'productos.f_tipo_afectacion_id',
            'almacen_productos.stock_fisico',
            'almacen_productos.stock_subcantidad_fisico',
            'almacen_productos.stock_disponible',
            'almacen_productos.stock_subcantidad_disponible'
        )
        ->orderBy('marcas.id', 'asc')
        ->get();
        return $almacen_productos;
    }

    public function headings(): array {
        return [
            'almacen_id',
            'marca_id',
            'marca_nombre',
            'producto_id',
            'producto_nombre',
            'deleted_at',
            'factor',
            'peso',
            'tipo',
            'f_tipo_afectacion_id',
            'stock_fisico',
            'stock_subcantidad_fisico',
            'stock_disponible',
            'stock_subcantidad_disponible',
        ];
    }
}
