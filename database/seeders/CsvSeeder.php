<?php

namespace Database\Seeders;

use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CsvSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            // Crear archivo SQLite temporal
            $tempFilePath = storage_path('app/temp_db.sqlite');
            if (File::exists($tempFilePath)) {
                File::delete($tempFilePath); // Asegúrate de que no exista previamente
            }
            touch($tempFilePath);

            // Configurar SQLite para usar el archivo temporal
            config(['database.connections.sqlite-temp' => [
                'driver' => 'sqlite',
                'database' => $tempFilePath,
                'prefix' => '',
            ]]);
            DB::setDefaultConnection('sqlite-temp');

            $namefile = ['marcas-cod.csv', 'productos-cod.csv', 'rutas-cod.csv', 'padrones-cod.csv', 'clientes-cod.csv'];
            $path = storage_path('app/database/');
            echo "    delimitador de archivo: ( ; ) \n";
            $delimitador = ';';

            foreach ($namefile as $file) {
                echo "    " . $file . "\n";
                $csvFile = fopen($path . $file, 'r');
                $headers = fgetcsv($csvFile, null, $delimitador); // Obtener los encabezados del archivo CSV
                $eliminar = false;
                if ($headers[0] == 'id') {
                    $eliminar = true;
                    unset($headers[0]);
                }
                if (!$headers) {
                    throw new Exception("El archivo CSV no contiene encabezados.");
                }
                $headers = array_map(function($valor) {
                    return str_replace('-', '_', Str::slug(trim($valor)));
                }, $headers);
                // print_r($headers);
                $nameTable = explode('-', $file)[0] . "_temporales";
                // Crear la tabla dinámica con base en los encabezados
                $columns = array_map(fn($header) => "`".$header."` TEXT", $headers);
                array_unshift($columns, "`id` INTEGER PRIMARY KEY AUTOINCREMENT");
                $columns[] = "`nuevo_id` TEXT UNIQUE";
                // print_r($columns);
                // echo "    " . implode(", ", $columns) . "\n";
                $createTableQuery = "CREATE TABLE " . $nameTable . " (" . implode(", ", $columns) . ")";
                DB::statement($createTableQuery);

                // Insertar datos del CSV en la tabla
                while (($row = fgetcsv($csvFile, null, $delimitador)) !== false) {
                    if ($eliminar) {
                        unset($row[0]);
                    }
                    $data = array_combine($headers, $row);
                    //print_r($data);
                    DB::table($nameTable)->insert($data);
                    // DB::table($nameTable)->get()->first();
                    // break;

                }

                fclose($csvFile);
            }

            /*
            DB::setDefaultConnection('sqlite-temp');
            // Realizar consultas (ejemplo)
            $criterio = 'valor_buscado'; // Cambiar por el valor deseado
            $registro = DB::table('datos_temporales')
            ->where('columna1', $criterio) // Cambia "columna1" por la columna que necesitas consultar
            ->first();

            if ($registro) {
                print_r($registro);
            } else {
                echo "No se encontró el registro.";
            }
            DB::setDefaultConnection('mysql');
            // Eliminar el archivo temporal cuando termines
            File::delete($tempFilePath);
            */
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . " \n";
        } finally {
            DB::setDefaultConnection('mysql');
        }
    }
}
