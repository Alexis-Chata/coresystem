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
            ['codigo' => 'PROV001', 'name' => 'MOLITALIA S.A.C.'],
            ['codigo' => 'PROV002', 'name' => 'CONFIPERU S.A.C.'],
            ['codigo' => 'PROV003', 'name' => 'KRAFT FOOTS S.A.C.'],
            ['codigo' => 'PROV004', 'name' => '2 CERRITOS S.A.C.'],
            ['codigo' => 'PROV005', 'name' => 'VICTORIA S.A.C.'],
            ['codigo' => 'PROV006', 'name' => 'PEPSI S.A.C.'],
            ['codigo' => 'PROV007', 'name' => 'WINTERS S.A.C.'],
            ['codigo' => 'PROV008', 'name' => 'GLAX S.A.C.'],
            ['codigo' => 'PROV009', 'name' => 'COLOMBINA S.A.C.'],
            ['codigo' => 'PROV010', 'name' => 'ARCOR S.A.C.'],
            ['codigo' => 'PROV011', 'name' => 'CAMPO NORTE S.A.C.'],
            ['codigo' => 'PROV012', 'name' => 'YOMAR S.A.C'],
            ['codigo' => 'PROV013', 'name' => 'SALVATTORE S.A.C.'],
            ['codigo' => 'PROV014', 'name' => 'DELICORP S.A.C'],
            ['codigo' => 'PROV015', 'name' => 'KOKI S.A.C.'],
            ['codigo' => 'PROV016', 'name' => 'FINI S.A.C.'],
            ['codigo' => 'PROV017', 'name' => 'SAN JORGE S.A.C.'],
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
