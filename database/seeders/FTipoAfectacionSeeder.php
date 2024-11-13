<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FTipoAfectacion;

class FTipoAfectacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiposAfectacion = [
            [
                'name' => 'Gravado',
                'descripcion' => 'Operación Gravada',
                'letra' => 'G',
                'codigo' => '10',
                'tipo' => 'Gravado'
            ],
            [
                'name' => 'Exonerado',
                'descripcion' => 'Operación Exonerada',
                'letra' => 'E',
                'codigo' => '20',
                'tipo' => 'Exonerado'
            ],
            [
                'name' => 'Inafecto',
                'descripcion' => 'Operación Inafecta',
                'letra' => 'I',
                'codigo' => '30',
                'tipo' => 'Inafecto'
            ],
            // Puedes agregar más tipos de afectación según sea necesario
        ];

        foreach ($tiposAfectacion as $tipo) {
            FTipoAfectacion::create($tipo);
        }
    }
}
