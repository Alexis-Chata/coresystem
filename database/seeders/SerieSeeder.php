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
            'serie' => 'F001',
            'correlativo' => 0,
            'fechaemision' => now(),
            'f_sede_id' => 1,
            'f_tipo_comprobante_id' => 1,
        ]);

        FSerie::create([
            'serie' => 'F002',
            'correlativo' => 0,
            'fechaemision' => now(),
            'f_sede_id' => 1,
            'f_tipo_comprobante_id' => 1,
        ]);

        FSerie::create([
            'serie' => 'B001',
            'correlativo' => 0,
            'fechaemision' => now(),
            'f_sede_id' => 1,
            'f_tipo_comprobante_id' => 2,
        ]);

        FSerie::create([
            'serie' => 'B002',
            'correlativo' => 0,
            'fechaemision' => now(),
            'f_sede_id' => 1,
            'f_tipo_comprobante_id' => 2,
        ]);

        FSerie::create([
            'serie' => 'FC01',
            'correlativo' => 0,
            'fechaemision' => now(),
            'f_sede_id' => 1,
            'f_tipo_comprobante_id' => 3,
        ]);

        FSerie::create([
            'serie' => 'BC01',
            'correlativo' => 0,
            'fechaemision' => now(),
            'f_sede_id' => 1,
            'f_tipo_comprobante_id' => 3,
        ]);
    }
}
