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
            'ruc' => '20123456789',
            'razon_social' => 'GoloMix S.A.C.',
            'name_comercial' => 'GoloMix',
            'direccion' => 'Av. Principal 123, Lima',
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
