<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\Empresa;
use App\Models\Marca;
use App\Models\Categoria;
use App\Models\FTipoAfectacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

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

        $tipoAfectacion = FTipoAfectacion::first();
        if (!$tipoAfectacion) {
            throw new \Exception('No hay tipos de afectación en la base de datos. Asegúrate de ejecutar FTipoAfectacionSeeder primero.');
        }

        // Ruta del archivo SQL
        $sqlPath = storage_path('app/database/productos.sql');

        // Verificar si el archivo existe
        if (File::exists($sqlPath)) {
            // Leer el contenido del archivo
            $sql = File::get($sqlPath);

            // Ejecutar el SQL
            DB::unprepared($sql);

            $this->command->info('  Se ejecutó correctamente el archivo SQL.');
        } else {
            $this->command->error('El archivo SQL no existe en la ruta especificada.');
        }

        // $productos = [
        //     [
        //         'name' => 'producto 1',
        //         'empresa_id' => $empresa->id,
        //         'marca_id' => $marca->id,
        //         'categoria_id' => $categoria->id,
        //         'f_tipo_afectacion_id' => $tipoAfectacion->id,
        //         'porcentaje_igv' => '18',
        //         'cantidad' => 1,
        //     ],
        //     [
        //         'name' => 'producto 2',
        //         'empresa_id' => $empresa->id,
        //         'marca_id' => $marca->id,
        //         'categoria_id' => $categoria->id,
        //         'f_tipo_afectacion_id' => $tipoAfectacion->id,
        //         'porcentaje_igv' => '18',
        //         'cantidad' => 1,
        //     ],
        //     // Añade más productos según sea necesario
        // ];

        // foreach ($productos as $producto) {
        //     Producto::create($producto);
        // }
    }
}
