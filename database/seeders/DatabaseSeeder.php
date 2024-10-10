<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Empresa;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call([
            EmpresaSeeder::class,
            FTipoDocumentoSeeder::class,
            EmpleadoSeeder::class,
            // ... otros seeders
        ]);

        $empresa = Empresa::first();
        if ($empresa) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'empresa_id' => $empresa->id,
            ]);
        } else {
            throw new \Exception('No hay empresas en la base de datos. Asegúrate de ejecutar EmpresaSeeder primero.');
        }
    }
}
