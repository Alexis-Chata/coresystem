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

        DB::insert("insert  into `marcas`(`id`,`codigo`,`name`,`empresa_id`) values
        (1,NULL,'MOLITALIA',1),
        (2,NULL,'CONFIPERU',1),
        (3,NULL,'KRAFT FOOTS',1),
        (4,NULL,'2 CERRITOS',1),
        (5,NULL,'VICTORIA',1),
        (6,NULL,'PEPSI',1),
        (7,NULL,'WINTERS',1),
        (8,NULL,'GLAX',1),
        (9,NULL,'COLOMBINA',1),
        (10,NULL,'ARCOR',1),
        (11,NULL,'CAMPO NORTE',1),
        (12,NULL,'YOMAR SAC',1),
        (13,NULL,'SALVATTORE',1),
        (14,NULL,'DELICORP SAC',1),
        (15,NULL,'KOKI',1),
        (16,NULL,'FINI',1),
        (17,NULL,'SAN JORGE',1);
        ");

        $marcas = [
            ['codigo' => 'M001', 'name' => 'Marca A'],
            ['codigo' => 'M002', 'name' => 'Marca B'],
            ['codigo' => 'M003', 'name' => 'Marca C'],
            // Añade más marcas según sea necesario
        ];

        foreach ($marcas as $marca) {
            Marca::create([
                'codigo' => $marca['codigo'],
                'name' => $marca['name'],
                'empresa_id' => $empresa->id,
            ]);
        }
    }
}
