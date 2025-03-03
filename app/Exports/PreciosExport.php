<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PreciosExport implements FromCollection, WithHeadings, ShouldAutoSize, WithColumnFormatting
{
    private $lista_precio_id;

    public function __construct($lista_precio_id)
    {
        $this->lista_precio_id = $lista_precio_id;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $precios = DB::table('producto_lista_precios')
            ->join('productos', 'productos.id', '=', 'producto_lista_precios.producto_id')
            ->join('marcas', 'marcas.id', '=', 'productos.marca_id')
            ->select([
                'productos.id as producto_codigo',
                'productos.name as producto_name',
                'producto_lista_precios.precio as precio_cj',
                'marcas.id as marca_codigo',
                'productos.cantidad as cant_cj',
                DB::raw('producto_lista_precios.precio / productos.cantidad as precio_unid'),
                'marcas.name as marca_name',
            ])
            ->where('producto_lista_precios.lista_precio_id', '=', $this->lista_precio_id)
            ->where('productos.deleted_at', '=', null)
            ->orderBy('marcas.id', 'asc')
            ->get();
        //dd($precios);
        return $precios;
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_NUMBER_00, // Columna "Precio Cj" (C)
            'F' => NumberFormat::FORMAT_NUMBER_00, // Columna "Precio Unid" (F)
        ];
    }

    public function headings(): array
    {
        $name = match ($this->lista_precio_id) {
            1 => 'Bodega',
            2 => 'Mayorista',
            default => 'Default',
        };
        return [
            ['Lista de Precios ' . $name],
            [
                'Codigo Producto',
                'Producto ' . $name,
                'Precio Cj',
                'Codigo Marca',
                'Cant Cj',
                'Precio Unid',
                'Marca'
            ]
        ];
    }
}
