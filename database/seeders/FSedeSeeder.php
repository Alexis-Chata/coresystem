<?php

namespace Database\Seeders;

use App\Models\FSede;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FSedeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FSede::create([
            "name" => "Golomix Principal",
            "telefono" => "999666999",
            "direccion" => "MZA. Q LOTE. 7 C.C. JICAMARCA SCT EL PEDREGAL ANEXO 22 (ESPALDA DE PISCINA EL PADRINO) LIMA - HUAROCHIRI - SAN ANTONIO",
            "departamento" => "LIMA",
            "provincia" => "HUAROCHIRI ",
            "distrito" => "SAN ANTONIO",
            "ubigueo" => "150604",
            "addresstypecode" => "0000",
            "empresa_id" => 1,
        ]);
    }
}
