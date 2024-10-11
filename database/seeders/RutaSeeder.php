<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ruta;
use App\Models\Empleado;
use App\Models\Empresa;

class RutaSeeder extends Seeder
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

        $vendedores = Empleado::where('tipo_empleado', 'vendedor')->get();
        if ($vendedores->isEmpty()) {
            throw new \Exception('No hay vendedores en la base de datos. Asegúrate de ejecutar EmpleadoSeeder primero.');
        }

        $rutas = [
            ['codigo' => 'R001', 'name' => 'Ruta Lima Norte'],
            ['codigo' => 'R002', 'name' => 'Ruta Lima Sur'],
            ['codigo' => 'R003', 'name' => 'Ruta Lima Este'],
        ];

        foreach ($rutas as $index => $ruta) {
            Ruta::create([
                'codigo' => $ruta['codigo'],
                'name' => $ruta['name'],
                'vendedor_id' => $vendedores[$index % $vendedores->count()]->id,
                'empresa_id' => $empresa->id,
            ]);
        }
    }
}
