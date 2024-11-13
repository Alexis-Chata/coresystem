<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FTipoDocumento;

class FTipoDocumentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FTipoDocumento::create(['codigo' => 1, 'tipo_documento' => 'DNI', 'name' => 'Documento Nacional de Identidad']);
        FTipoDocumento::create(['codigo' => 4, 'tipo_documento' => 'CE', 'name' => 'Carnet de Extranjería']);
        FTipoDocumento::create(['codigo' => 7, 'tipo_documento' => 'PAS', 'name' => 'Pasaporte']);
        FTipoDocumento::create(['codigo' => 6, 'tipo_documento' => 'RUC', 'name' => 'Registro Único de Contribuyentes']);
        // Agrega más tipos de documentos según sea necesario
    }
}
