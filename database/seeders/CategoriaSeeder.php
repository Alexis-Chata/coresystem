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
            ['nombre' => 'Caramelos', 'descripcion' => 'Dulces de diferentes sabores y colores.'],
            ['nombre' => 'Chicles', 'descripcion' => 'Gomas de mascar con diversos sabores.'],
            ['nombre' => 'Gomas y gomitas', 'descripcion' => 'Golosinas masticables de diferentes formas y sabores.'],
            ['nombre' => 'Chocolates', 'descripcion' => 'Productos elaborados a base de cacao.'],
            ['nombre' => 'Chupetines', 'descripcion' => 'Dulces con palo, también conocidos como paletas.'],
            ['nombre' => 'Galletas dulces', 'descripcion' => 'Galletas con sabores dulces.'],
            ['nombre' => 'Galletas saladas', 'descripcion' => 'Galletas con sabores salados.'],
            ['nombre' => 'Wafers', 'descripcion' => 'Galletas crujientes con relleno.'],
            ['nombre' => 'Snacks dulces', 'descripcion' => 'Bocadillos con sabores dulces.'],
            ['nombre' => 'Barras de cereal', 'descripcion' => 'Barras hechas de cereales y otros ingredientes.'],
            ['nombre' => 'Refrescos y sodas', 'descripcion' => 'Bebidas carbonatadas y refrescantes.'],
            ['nombre' => 'Bebidas en polvo', 'descripcion' => 'Bebidas que se preparan disolviendo polvo en agua.'],
            ['nombre' => 'Bebidas energéticas', 'descripcion' => 'Bebidas que proporcionan energía adicional.'],
            ['nombre' => 'Panetones', 'descripcion' => 'Pan dulce tradicional de Navidad.'],
            ['nombre' => 'Productos para regalo', 'descripcion' => 'Artículos ideales para regalar.'],
            ['nombre' => 'Exhibidores y bonificaciones', 'descripcion' => 'Materiales para exhibir productos y promociones.'],
            ['nombre' => 'Noel', 'descripcion' => 'Productos de la marca Noel.']
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
