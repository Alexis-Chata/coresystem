<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empresa;

class EmpresaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Empresa::create([
            'ruc' => '20603752458',
            'razon_social' => 'L & L GOLOMIX E.I.R.L.',
            'name_comercial' => 'L & L GOLOMIX E.I.R.L.',
            'direccion' => 'MZA. Q LOTE. 7 C.C. JICAMARCA SCT EL PEDREGAL ANEXO 22 (ESPALDA DE PISCINA EL PADRINO) LIMA - HUAROCHIRI - SAN ANTONIO',
            // Estos campos no estaban en tu seeder original, pero los incluyo por si los necesitas
            'logo_path' => null,
            'cert_path' => null,
            'sol_user' => null,
            'sol_pass' => null,
            'client_id' => null,
            'client_secret' => null,
            'production' => false,
        ]);
    }
}
