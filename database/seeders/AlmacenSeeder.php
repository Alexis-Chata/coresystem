<?php

namespace Database\Seeders;

use App\Models\Almacen;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AlmacenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Almacen::create([
            'name' => 'Almacén Principal',
            'direccion' => 'MZA. Q LOTE. 7 C.C. JICAMARCA SCT EL PEDREGAL ANEXO 22 (ESPALDA DE PISCINA EL PADRINO) LIMA - HUAROCHIRI - SAN ANTONIO',
            'telefono' => '999888999',
            'email' => 'almacen@example.com',
            'encargado_id' => '1',
            'empresa_id' => '1',
        ]);
    }
}
