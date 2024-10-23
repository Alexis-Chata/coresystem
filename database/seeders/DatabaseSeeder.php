<?php

namespace Database\Seeders;

use App\Models\User;
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
            VehiculoSeeder::class,
            EmpleadoSeeder::class,
            FTipoComprobanteSeeder::class,
            ListaPrecioSeeder::class,
            RutaSeeder::class,
            ClienteSeeder::class,
            MarcaSeeder::class,
            CategoriaSeeder::class,
            FTipoAfectacionSeeder::class,
            ProductoSeeder::class,
            PedidoSeeder::class,
            PermissionSeeder::class,
            ProveedorSeeder::class,
        ]);

        $empresa = Empresa::first();
        if ($empresa) {
            // Verifica si el usuario ya existe
            $testUser = User::where('email', 'test@example.com')->first();

            if (!$testUser) {
                User::factory()->create([
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'empresa_id' => $empresa->id,
                ]);
            }
        } else {
            throw new \Exception('No hay empresas en la base de datos. Asegúrate de ejecutar EmpresaSeeder primero.');
        }
    }
}
