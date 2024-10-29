<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Padron;
use App\Models\Cliente;
use App\Models\Ruta;

class PadronSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener todos los clientes y rutas
        $clientes = Cliente::all();
        $rutas = Ruta::all();

        // Crear 10 padrones aleatorios
        for ($i = 0; $i < 10; $i++) {
            Padron::create([
                'cliente_id' => $clientes->random()->id,
                'ruta_id' => $rutas->random()->id,
                'nro_secuencia' => $i + 1,
            ]);
        }
    }
}
