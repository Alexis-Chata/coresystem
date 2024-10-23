<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Proveedor;
use App\Models\Empresa;

class ProveedorSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::first();
        if (!$empresa) {
            throw new \Exception('No hay empresas en la base de datos. Asegúrate de ejecutar EmpresaSeeder primero.');
        }

        $proveedores = [
            ['codigo' => 'PROV001', 'name' => 'Distribuidora Peruana S.A.C.'],
            ['codigo' => 'PROV002', 'name' => 'Importaciones Lima E.I.R.L.'],
            ['codigo' => 'PROV003', 'name' => 'Comercial Andina S.R.L.'],
            ['codigo' => 'PROV004', 'name' => 'Industrias del Sur S.A.'],
            ['codigo' => 'PROV005', 'name' => 'Corporación Nacional de Abastecimiento'],
        ];

        foreach ($proveedores as $proveedor) {
            Proveedor::create([
                'codigo' => $proveedor['codigo'],
                'name' => $proveedor['name'],
                'empresa_id' => $empresa->id,
            ]);
        }
    }
}