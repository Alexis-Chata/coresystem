<?php

namespace App\Exports;

use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductosExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $productos = DB::table('productos')
            ->join('marcas', 'productos.marca_id', '=', 'marcas.id')
            ->select([
                'marcas.id as marca_codigo',
                'marcas.name as marca_name',
                'productos.id as producto_codigo',
                'productos.name as producto_name',
                'productos.deleted_at as producto_deleted_at',
            ])
            ->orderBy('marcas.id', 'asc')
            ->get();

        return $productos;
    }

    public function headings(): array
    {
        return [
            ['Lista de Productos'],
            [
                'Codigo Marca',
                'Marca',
                'Codigo Producto',
                'Producto',
                'Estado',
            ]
        ];
    }
}
