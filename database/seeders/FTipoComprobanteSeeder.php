<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\F_tipo_comprobante;

class FTipoComprobanteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiposComprobante = [
            ['tipo_comprobante' => '01', 'name' => 'Factura', 'estado' => true],
            ['tipo_comprobante' => '03', 'name' => 'Boleta de Venta', 'estado' => true],
            ['tipo_comprobante' => '07', 'name' => 'Nota de Crédito', 'estado' => true],
            ['tipo_comprobante' => '08', 'name' => 'Nota de Débito', 'estado' => true],
        ];

        foreach ($tiposComprobante as $tipo) {
            F_tipo_comprobante::create($tipo);
        }
    }
}
