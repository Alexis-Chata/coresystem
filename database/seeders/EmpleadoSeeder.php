<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empleado;
use App\Models\F_tipo_documento;
use App\Models\Empresa;

class EmpleadoSeeder extends Seeder
{
    public function run(): void
    {
        $tipoDocumento = F_tipo_documento::inRandomOrder()->first();
        
        if (!$tipoDocumento) {
            throw new \Exception('No hay tipos de documento en la base de datos. Asegúrate de ejecutar FTipoDocumentoSeeder primero.');
        }

        $empresa = Empresa::first();
        if (!$empresa) {
            throw new \Exception('No hay empresas en la base de datos. Asegúrate de ejecutar EmpresaSeeder primero.');
        }

        Empleado::factory()->count(10)->create([
            'f_tipo_documento_id' => $tipoDocumento->id,
            'empresa_id' => $empresa->id,
        ]);
    }
}
