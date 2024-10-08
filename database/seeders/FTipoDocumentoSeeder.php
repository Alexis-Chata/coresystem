<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\F_tipo_documento;

class FTipoDocumentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        F_tipo_documento::create(['tipo_documento' => 'DNI', 'name' => 'Documento Nacional de Identidad']);
        F_tipo_documento::create(['tipo_documento' => 'CE', 'name' => 'Carnet de Extranjería']);
        F_tipo_documento::create(['tipo_documento' => 'PAS', 'name' => 'Pasaporte']);
        // Agrega más tipos de documentos según sea necesario
    }
}
