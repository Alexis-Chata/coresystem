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
        $clientes = Cliente::all();
        $rutas = Ruta::all();

        foreach ($clientes as $index => $cliente) {
            Padron::create([
                'cliente_id' => $cliente->id,
                'ruta_id' => $rutas->random()->id,
                'nro_secuencia' => $index + 1,
            ]);
        }
    }
}
