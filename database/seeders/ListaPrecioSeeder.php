<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empresa;
use App\Models\Lista_precio;

class ListaPrecioSeeder extends Seeder
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

        Lista_precio::create([
            'name' => 'Lista de Precios Estándar',
            'descripcion' => 'Lista de precios por defecto para todos los clientes',
            'empresa_id' => $empresa->id,
        ]);

        Lista_precio::create([
            'name' => 'Lista de Precios VIP',
            'descripcion' => 'Lista de precios para clientes preferenciales',
            'empresa_id' => $empresa->id,
        ]);
    }
}
