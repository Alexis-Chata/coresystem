<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ruta;
use App\Models\Empleado;
use App\Models\Empresa;
use App\Models\ListaPrecio;
use Illuminate\Support\Facades\DB;

class RutaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ubica tu archivo CSV
        $csvFile = storage_path('app/database/rutas.csv');

        // Abre el archivo en modo lectura
        if (($handle = fopen($csvFile, 'r')) !== false) {
            // Leer la primera línea para obtener los encabezados
            $headers = fgetcsv($handle);

            // Iterar sobre las filas del archivo
            while (($data = fgetcsv($handle)) !== false) {
                // Combina los encabezados con los valores
                $row = array_combine($headers, $data);
                $ruta = new Ruta();
                $ruta->fill([
                    'name'=>$row['name'],
                    'dia_visita'=>$row['dia_visita'],
                    'vendedor_id'=>$row['vendedor_id'],
                    'empresa_id'=>$row['empresa_id'],
                    'lista_precio_id'=>$row['lista_precio_id'],
                ]);
                $ruta->save();
            }
            // Cierra el archivo
            fclose($handle);
        }
    }
}
