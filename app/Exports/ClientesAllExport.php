<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClientesAllExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $Clientes = DB::table('padrons')
            ->join('clientes', 'clientes.id', '=', 'padrons.cliente_id')
            ->join('rutas', 'rutas.id', '=', 'padrons.ruta_id')
            ->join('empleados', 'empleados.id', '=', 'rutas.vendedor_id')
            ->select([
                'padrons.cliente_id',
                'padrons.ruta_id',
                'padrons.nro_secuencia',
                'rutas.name as ruta_name',
                'clientes.razon_social',
                'clientes.direccion',
                'clientes.numero_documento',
                'clientes.lista_precio_id',
                'rutas.vendedor_id',
                'empleados.name as empleado-name',
            ])
            ->orderBy('padrons.ruta_id', 'asc')
            ->orderBy('padrons.nro_secuencia', 'asc')
            //->take(5)
            ->get();
        return $Clientes;
    }

    public function headings(): array {
        return [
            'Codigo Cliente',
            'Ruta',
            'Secuencia',
            'Nombre Ruta',
            'Nombre Cliente',
            'Direccion',
            'Ruc / Dni',
            'LP',
            'Codigo Vendedor',
            'Vendedor'
        ];
    }
}
