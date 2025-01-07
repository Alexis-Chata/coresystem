<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\Empresa;
use App\Models\Marca;
use App\Models\Categoria;
use App\Models\FTipoAfectacion;
use Exception;
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

        DB::setDefaultConnection('sqlite-temp');
        // Realizar consultas (ejemplo)
        $registros = DB::table('productos_temporales')->get();
        DB::setDefaultConnection('mysql');

        $bonificacion = ['810','552','914','915','008','859','496','165','927','040','476','976','059','803','848','811','515','563','758','101','539','998','997','994','404','828','814','770','819','969','913','983','477','648','957','936','623','010','547','982','907','060','855','723','726','577','544','543','838','053','645','780','749','965','860','616','870','599','026','034','821','716','973','851','831','228','416','506','413','009','353','360','351','359','184',
        ];
        foreach ($registros as $registro) {
                if (in_array(str_pad($registro->cequiv, 3, '0', STR_PAD_LEFT), $bonificacion)) {
                    $registro->f_tipo_afectacion_id = 21;
                }
                $modelo = new Producto();
                $modelo->fill([
                    'name' => $registro->tcor,
                    'cantidad' => $registro->qfaccon,
                    'sub_cantidad' => $registro->sub_cantidad ?? 0,
                    'peso' => $registro->peso ?? 0,
                    'tipo' => $registro->tipo ?? 'estandar',
                    'empresa_id' => $registro->empresa_id ?? 1,
                    'marca_id' => $registro->marca_id ?? 1,
                    'categoria_id' => $registro->categoria_id ?? 1,
                    'f_tipo_afectacion_id' => $registro->f_tipo_afectacion_id ?? 10,
                    'porcentaje_igv' => $registro->porcentaje_igv ?? 18,
                    'tipo_unidad' => $registro->tipo_unidad ?? 'NIU',
                ]);
                $modelo->save();

                // Agregar precios en la tabla intermedia
                $modelo->listaPrecios()->attach([
                    1 => ['precio' => number_format_punto2($registro->qprecio)], // Donde 1 es el ID de la lista de precios
                    2 => ['precio' => number_format_punto2($registro->qprecio2)], // Donde 2 es otro ID de lista de precios
                ]);

                DB::setDefaultConnection('sqlite-temp');
                // Realizar consultas (ejemplo)
                DB::table('productos_temporales')->where('id', $registro->id)->update(['nuevo_id' => $modelo->id]);
                DB::setDefaultConnection('mysql');

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
