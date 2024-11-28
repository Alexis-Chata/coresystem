<?php

namespace Database\Seeders;

use App\Models\TipoMovimiento;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoMovimientoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TipoMovimiento::create([
            'codigo' => '100',
            'name' => 'ing. compras',
            'descripcion' => 'Ingreso de mercadería al almacén por compras',
            'tipo' => 'ingreso',
            'empleado_id' => '1',
        ]);

        TipoMovimiento::create([
            'codigo' => '101',
            'name' => 'ing. reparto sujeta a liquidacion',
            'descripcion' => 'Ingreso de mercadería al almacén por reparto sujeta a liquidación',
            'tipo' => 'ingreso',
            'empleado_id' => '1',
        ]);

        TipoMovimiento::create([
            'codigo' => '102',
            'name' => 'ing. por ajuste de inventario',
            'descripcion' => 'Ingreso de mercadería al almacén por ajuste de inventario',
            'tipo' => 'ingreso',
            'empleado_id' => '1',
        ]);

        TipoMovimiento::create([
            'codigo' => '103',
            'name' => 'ing. por transferencia',
            'descripcion' => 'Ingreso de mercadería al almacén por transferencia',
            'tipo' => 'ingreso',
            'empleado_id' => '1',
        ]);

        TipoMovimiento::create([
            'codigo' => '104',
            'name' => 'ing. cambio de presentacion',
            'descripcion' => 'Ingreso de mercadería al almacén por cambio de presentacion',
            'tipo' => 'ingreso',
            'empleado_id' => '1',
        ]);

        TipoMovimiento::create([
            'codigo' => '200',
            'name' => 'sal. por ventas',
            'descripcion' => 'Salida de mercadería al almacén por ventas',
            'tipo' => 'salida',
            'empleado_id' => '1',
        ]);

        TipoMovimiento::create([
            'codigo' => '201',
            'name' => 'sal. reparto sujeta a liquidacion',
            'descripcion' => 'Salida de mercadería al almacén por reparto sujeta a liquidación',
            'tipo' => 'salida',
            'empleado_id' => '1',
        ]);

        TipoMovimiento::create([
            'codigo' => '202',
            'name' => 'sal. por ajuste de inventario',
            'descripcion' => 'Salida de mercadería al almacén por ajuste de inventario',
            'tipo' => 'salida',
            'empleado_id' => '1',
        ]);

        TipoMovimiento::create([
            'codigo' => '203',
            'name' => 'sal. por transferencia',
            'descripcion' => 'Salida de mercadería al almacén por transferencia',
            'tipo' => 'salida',
            'empleado_id' => '1',
        ]);

        TipoMovimiento::create([
            'codigo' => '204',
            'name' => 'sal. cambio de presentacion',
            'descripcion' => 'Salida de mercadería al almacén por cambio de presentacion',
            'tipo' => 'salida',
            'empleado_id' => '1',
        ]);

        TipoMovimiento::create([
            'codigo' => '205',
            'name' => 'sal. vencimiento de producto',
            'descripcion' => 'Salida de mercadería al almacén por vencimiento de producto',
            'tipo' => 'salida',
            'empleado_id' => '1',
        ]);

        TipoMovimiento::create([
            'codigo' => '206',
            'name' => 'sal. consumo interno',
            'descripcion' => 'Salida de mercadería al almacén por consumo interno',
            'tipo' => 'salida',
            'empleado_id' => '1',
        ]);

        TipoMovimiento::create([
            'codigo' => '207',
            'name' => 'sal. por ajuste de inventario',
            'descripcion' => 'Salida de mercadería al almacén por ajuste de inventario',
            'tipo' => 'salida',
            'empleado_id' => '1',
        ]);

        TipoMovimiento::create([
            'codigo' => '208',
            'name' => 'sal. faltante en caja/paquete',
            'descripcion' => 'Salida de mercadería al almacén por faltante en caja/paquete',
            'tipo' => 'salida',
            'empleado_id' => '1',
        ]);
    }
}
