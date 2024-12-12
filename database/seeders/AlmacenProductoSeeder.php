<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AlmacenProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productos = Producto::all();
        foreach ($productos as $producto) {
            $producto->almacenProductos()->create(['almacen_id' => '1', 'stock_disponible' => 5, 'stock_fisico'=> 5]);
        }
    }
}
