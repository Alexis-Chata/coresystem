<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ruta;
use Illuminate\Support\Facades\DB;

class RutaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::setDefaultConnection('sqlite-temp');
        // Realizar consultas (ejemplo)
        $registros = DB::table('rutas_temporales')->whereNot('dia_visita', '-')->get();
        DB::setDefaultConnection('mysql');

        foreach ($registros as $registro) {
            $modelo = new Ruta();
            $modelo->fill([
                'name' => $registro->tdes,
                'dia_visita' => $registro->dia_visita,
                'vendedor_id' => $registro->vendedor_id,
                'empresa_id' => $registro->empresa_id,
                'lista_precio_id' => $registro->lista_precio_id,
            ]);
            $modelo->save();

            DB::setDefaultConnection('sqlite-temp');
            // Realizar consultas (ejemplo)
            DB::table('rutas_temporales')->where('id', $registro->id)->update(['nuevo_id' => $modelo->id]);
            DB::setDefaultConnection('mysql');
        }
    }
}
