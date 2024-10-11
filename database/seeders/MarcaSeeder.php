<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Marca;
use App\Models\Empresa;

class MarcaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $empresa = Empresa::first();
        if (!$empresa) {
            throw new \Exception('No hay empresas en la base de datos. Asegúrate de ejecutar EmpresaSeeder primero.');
        }

        $marcas = [
            ['codigo' => 'M001', 'name' => 'Marca A'],
            ['codigo' => 'M002', 'name' => 'Marca B'],
            ['codigo' => 'M003', 'name' => 'Marca C'],
            // Añade más marcas según sea necesario
        ];

        foreach ($marcas as $marca) {
            Marca::create([
                'codigo' => $marca['codigo'],
                'name' => $marca['name'],
                'empresa_id' => $empresa->id,
            ]);
        }
    }
}
