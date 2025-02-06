<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Marca;
use App\Models\Empresa;
use Illuminate\Support\Facades\DB;

class MarcaSeeder extends Seeder
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

        DB::setDefaultConnection('sqlite-temp');
        // Realizar consultas (ejemplo)
        $registros = DB::table('marcas_temporales')->get();
        DB::setDefaultConnection('mysql');

        $marcas = ['MOLITALIA', 'CONFIPERU', 'KRAFT FOOTS', '2 CERRITOS', 'VICTORIA', 'PEPSI', 'WINTERS', 'GLAX', 'LA GITANA', 'ARCOR', 'CAMPO NORTE', 'YOMAR SAC', 'SALVATTORE', 'DELICORP SAC', 'KOKI', 'FINI', 'SAN JORGE'];
        foreach ($registros as $registro) {
            if (in_array($registro->tdes, $marcas)) {
                $modelo = new Marca();
                $modelo->fill([
                    'name' => $registro->tdes,
                    'empresa_id' => 1,
                ]);
                $modelo->save();
                $modelo->nro_orden = $modelo->id;
                $modelo->save();

                DB::setDefaultConnection('sqlite-temp');
                // Realizar consultas (ejemplo)
                DB::table('marcas_temporales')->where('id', $registro->id)->update(['nuevo_id' => $modelo->id]);
                DB::setDefaultConnection('mysql');
            }
        }

        // $marcas = [
        //     ['codigo' => 'M001', 'name' => 'Marca A'],
        //     ['codigo' => 'M002', 'name' => 'Marca B'],
        //     ['codigo' => 'M003', 'name' => 'Marca C'],
        //     // Añade más marcas según sea necesario
        // ];

        // foreach ($marcas as $marca) {
        //     Marca::create([
        //         'codigo' => $marca['codigo'],
        //         'name' => $marca['name'],
        //         'empresa_id' => $empresa->id,
        //     ]);
        // }
    }
}
