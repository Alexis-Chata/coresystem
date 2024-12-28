<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehiculo;

class VehiculoSeeder extends Seeder
{
    public function run(): void
    {
        $vehiculos = [
            [
                'marca' => 'Toyota',
                'modelo' => 'Hilux',
                'placa' => 'ABC-123',
                'color' => 'Blanco',
                'certificado_inscripcion' => 'CI123456',
                'numero_tarjeta' => 'NT987654',
                'tonelaje_maximo' => '3.5',
            ],
            [
                'marca' => 'Hyundai',
                'modelo' => 'H100',
                'placa' => 'DEF-456',
                'color' => 'Gris',
                'certificado_inscripcion' => 'CI234567',
                'numero_tarjeta' => 'NT876543',
                'tonelaje_maximo' => '2.5',
            ],
            [
                'marca' => 'Mitsubishi',
                'modelo' => 'Fuso',
                'placa' => 'GHI-789',
                'color' => 'Azul',
                'certificado_inscripcion' => 'CI345678',
                'numero_tarjeta' => 'NT765432',
                'tonelaje_maximo' => '5.0',
            ],
            [
                'marca' => 'Toyota',
                'modelo' => 'Hilux',
                'placa' => 'ABC-123',
                'color' => 'Blanco',
                'certificado_inscripcion' => 'CI123456',
                'numero_tarjeta' => 'NT987654',
                'tonelaje_maximo' => '3.5',
            ],
            [
                'marca' => 'Hyundai',
                'modelo' => 'H100',
                'placa' => 'DEF-456',
                'color' => 'Gris',
                'certificado_inscripcion' => 'CI234567',
                'numero_tarjeta' => 'NT876543',
                'tonelaje_maximo' => '2.5',
            ],
            [
                'marca' => 'Mitsubishi',
                'modelo' => 'Fuso',
                'placa' => 'GHI-789',
                'color' => 'Azul',
                'certificado_inscripcion' => 'CI345678',
                'numero_tarjeta' => 'NT765432',
                'tonelaje_maximo' => '5.0',
            ],
            [
                'marca' => 'Toyota',
                'modelo' => 'Hilux',
                'placa' => 'ABC-123',
                'color' => 'Blanco',
                'certificado_inscripcion' => 'CI123456',
                'numero_tarjeta' => 'NT987654',
                'tonelaje_maximo' => '3.5',
            ],
            [
                'marca' => 'Hyundai',
                'modelo' => 'H100',
                'placa' => 'DEF-456',
                'color' => 'Gris',
                'certificado_inscripcion' => 'CI234567',
                'numero_tarjeta' => 'NT876543',
                'tonelaje_maximo' => '2.5',
            ],
            [
                'marca' => 'Mitsubishi',
                'modelo' => 'Fuso',
                'placa' => 'GHI-789',
                'color' => 'Azul',
                'certificado_inscripcion' => 'CI345678',
                'numero_tarjeta' => 'NT765432',
                'tonelaje_maximo' => '5.0',
            ],
        ];

        foreach ($vehiculos as $vehiculo) {
            Vehiculo::create($vehiculo);
        }
    }
}
