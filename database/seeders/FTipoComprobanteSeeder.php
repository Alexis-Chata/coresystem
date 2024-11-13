<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FTipoComprobante;

class FTipoComprobanteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiposComprobante = [
            ['tipo_comprobante' => "01", 'name' => 'Factura', 'estado' => true],
            ['tipo_comprobante' => "03", 'name' => 'Boleta de Venta', 'estado' => true],
            ['tipo_comprobante' => "07", 'name' => 'Nota de Crédito', 'estado' => true],
            ['tipo_comprobante' => "08", 'name' => 'Nota de Débito', 'estado' => true],
            ['tipo_comprobante' => "09", 'name' => 'Guia De Remisión Remitente', 'estado' => true],
        ];

        foreach ($tiposComprobante as $tipo) {
            FTipoComprobante::create($tipo);
        }
    }
}
