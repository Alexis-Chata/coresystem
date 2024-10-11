<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\Empresa;
use App\Models\Marca;
use App\Models\Categoria;
use App\Models\F_tipo_afectacion;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::first();
        if (!$empresa) {
            throw new \Exception('No hay empresas en la base de datos. Asegúrate de ejecutar EmpresaSeeder primero.');
        }

        $marca = Marca::first();
        if (!$marca) {
            throw new \Exception('No hay marcas en la base de datos. Asegúrate de ejecutar MarcaSeeder primero.');
        }

        $categoria = Categoria::first();
        if (!$categoria) {
            throw new \Exception('No hay categorías en la base de datos. Asegúrate de ejecutar CategoriaSeeder primero.');
        }

        $tipoAfectacion = F_tipo_afectacion::first();
        if (!$tipoAfectacion) {
            throw new \Exception('No hay tipos de afectación en la base de datos. Asegúrate de ejecutar FTipoAfectacionSeeder primero.');
        }

        $productos = [
            [
                'empresa_id' => $empresa->id,
                'marca_id' => $marca->id,
                'categoria_id' => $categoria->id,
                'f_tipo_afectacion_id' => $tipoAfectacion->id,
                'porcentaje_igv' => '18',
            ],
            [
                'empresa_id' => $empresa->id,
                'marca_id' => $marca->id,
                'categoria_id' => $categoria->id,
                'f_tipo_afectacion_id' => $tipoAfectacion->id,
                'porcentaje_igv' => '18',
            ],
            // Añade más productos según sea necesario
        ];

        foreach ($productos as $producto) {
            Producto::create($producto);
        }
    }
}
