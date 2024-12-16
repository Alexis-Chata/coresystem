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
                'id' => '10',
                'name' => 'Gravado - 10',
                'descripcion' => 'Operación Gravada',
                'letra' => 'S',
                'codigo' => '1000',
                'tipo' => 'VAT'
            ],
            [
                'id' => '20',
                'name' => 'Exonerado - 20',
                'descripcion' => 'Operación Exonerada',
                'letra' => 'E',
                'codigo' => '9997',
                'tipo' => 'VAT'
            ],
            [
                'id' => '30',
                'name' => 'Inafecto - 30',
                'descripcion' => 'Operación Inafecta',
                'letra' => 'O',
                'codigo' => '9998',
                'tipo' => 'FRE'
            ],
            [
                'id' => '21',
                'name' => 'Exonerado - 21',
                'descripcion' => 'Transferencia Gratuita',
                'letra' => 'Z',
                'codigo' => '9996',
                'tipo' => 'FRE'
            ],
            // Puedes agregar más tipos de afectación según sea necesario
        ];

        foreach ($tiposAfectacion as $tipo) {
            FTipoAfectacion::create($tipo);
        }
    }
}
