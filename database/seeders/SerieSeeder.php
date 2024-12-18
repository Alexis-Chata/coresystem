<?php

namespace Database\Seeders;

use App\Models\FSerie;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SerieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FSerie::create([
            'serie' => 'F010',
            'correlativo' => 0,
            'fechaemision' => now(),
            'f_sede_id' => 1,
            'f_tipo_comprobante_id' => 2,
        ]);

        FSerie::create([
            'serie' => 'F011',
            'correlativo' => 0,
            'fechaemision' => now(),
            'f_sede_id' => 1,
            'f_tipo_comprobante_id' => 2,
        ]);

        FSerie::create([
            'serie' => 'B010',
            'correlativo' => 0,
            'fechaemision' => now(),
            'f_sede_id' => 1,
            'f_tipo_comprobante_id' => 3,
        ]);

        FSerie::create([
            'serie' => 'B011',
            'correlativo' => 0,
            'fechaemision' => now(),
            'f_sede_id' => 1,
            'f_tipo_comprobante_id' => 3,
        ]);

        FSerie::create([
            'serie' => 'FC10',
            'correlativo' => 0,
            'fechaemision' => now(),
            'f_sede_id' => 1,
            'f_tipo_comprobante_id' => 4,
        ]);

        FSerie::create([
            'serie' => 'FC11',
            'correlativo' => 0,
            'fechaemision' => now(),
            'f_sede_id' => 1,
            'f_tipo_comprobante_id' => 4,
        ]);

        FSerie::create([
            'serie' => 'BC10',
            'correlativo' => 0,
            'fechaemision' => now(),
            'f_sede_id' => 1,
            'f_tipo_comprobante_id' => 4,
        ]);

        FSerie::create([
            'serie' => 'BC11',
            'correlativo' => 0,
            'fechaemision' => now(),
            'f_sede_id' => 1,
            'f_tipo_comprobante_id' => 4,
        ]);

        FSerie::create([
            'serie' => 'FD10',
            'correlativo' => 0,
            'fechaemision' => now(),
            'f_sede_id' => 1,
            'f_tipo_comprobante_id' => 5,
        ]);

        FSerie::create([
            'serie' => 'FD11',
            'correlativo' => 0,
            'fechaemision' => now(),
            'f_sede_id' => 1,
            'f_tipo_comprobante_id' => 5,
        ]);

        FSerie::create([
            'serie' => 'BD10',
            'correlativo' => 0,
            'fechaemision' => now(),
            'f_sede_id' => 1,
            'f_tipo_comprobante_id' => 5,
        ]);

        FSerie::create([
            'serie' => 'BD11',
            'correlativo' => 0,
            'fechaemision' => now(),
            'f_sede_id' => 1,
            'f_tipo_comprobante_id' => 5,
        ]);

        FSerie::create([
            'serie' => 'T010',
            'correlativo' => 0,
            'fechaemision' => now(),
            'f_sede_id' => 1,
            'f_tipo_comprobante_id' => 6,
        ]);

        FSerie::create([
            'serie' => 'T011',
            'correlativo' => 0,
            'fechaemision' => now(),
            'f_sede_id' => 1,
            'f_tipo_comprobante_id' => 6,
        ]);


        FSerie::create([
            'serie' => 'NP10',
            'correlativo' => 0,
            'fechaemision' => now(),
            'f_sede_id' => 1,
            'f_tipo_comprobante_id' => 1,
        ]);

        FSerie::create([
            'serie' => 'NP11',
            'correlativo' => 0,
            'fechaemision' => now(),
            'f_sede_id' => 1,
            'f_tipo_comprobante_id' => 1,
        ]);
    }
}
