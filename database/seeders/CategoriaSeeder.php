<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;
use App\Models\Empresa;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::first();

        if (!$empresa) {
            throw new \Exception('No hay empresas en la base de datos. Asegúrate de ejecutar EmpresaSeeder primero.');
        }

        $categorias = [
            ['nombre' => 'Electrónicos', 'descripcion' => 'Productos electrónicos'],
            ['nombre' => 'Ropa', 'descripcion' => 'Prendas de vestir'],
            ['nombre' => 'Alimentos', 'descripcion' => 'Productos alimenticios'],
            ['nombre' => 'Hogar', 'descripcion' => 'Artículos para el hogar'],
            ['nombre' => 'Deportes', 'descripcion' => 'Equipamiento deportivo'],
        ];

        foreach ($categorias as $categoria) {
            Categoria::create([
                'nombre' => $categoria['nombre'],
                'descripcion' => $categoria['descripcion'],
                'empresa_id' => $empresa->id,
            ]);
        }
    }
}
