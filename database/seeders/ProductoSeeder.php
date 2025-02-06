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
        $marcas_temporales = DB::table('marcas_temporales')->get();
        DB::setDefaultConnection('mysql');

        $bonificacion = ['9','10','26','34','40','53','59','60','101','165','184','228','351','353','359','360','404','416','476','477','496','506','515','539','543','544','547','552','563','577','599','616','623','648','716','723','726','749','770','780','810','811','814','821','831','838','848','851','855','859','860','870','907','913','914','915','927','936','957','965','969','973','976','982','983','997','998',
        ];
        foreach ($registros as $registro) {
                if (in_array(str_pad($registro->cequiv, 3, '0', STR_PAD_LEFT), $bonificacion)) {
                    $registro->f_tipo_afectacion_id = 21;
                }
                $marca_temp = $marcas_temporales->first(function ($item) use ($registro) {
                    return $item->ccod == $registro->cc05;
                });

                $modelo = new Producto();
                $modelo->fill([
                    'name' => $registro->tcor,
                    'cantidad' => $registro->qfaccon,
                    'sub_cantidad' => $registro->sub_cantidad ?? 0,
                    'peso' => $registro->peso ?? 0,
                    'tipo' => $registro->tipo ?? 'estandar',
                    'empresa_id' => $registro->cc4 ?? 1,
                    'marca_id' => $marca_temp->nuevo_id ?? 1,
                    'categoria_id' => $registro->categoria_id ?? 1,
                    'f_tipo_afectacion_id' => $registro->f_tipo_afectacion_id ?? 10,
                    'porcentaje_igv' => $registro->porcentaje_igv ?? 18,
                    'tipo_unidad' => $registro->tipo_unidad ?? 'NIU',
                ]);
                if ($registro->flagcre == 1) {
                    $modelo->deleted_at = now();
                }
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
