<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\FSede;
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
            RoleSeeder::class,
            EmpresaSeeder::class,
            FTipoDocumentoSeeder::class,
            VehiculoSeeder::class,
            FSedeSeeder::class,
            EmpleadoSeeder::class,
            FTipoComprobanteSeeder::class,
            ListaPrecioSeeder::class,
            RutaSeeder::class,
            ClienteSeeder::class,
            MarcaSeeder::class,
            CategoriaSeeder::class,
            FTipoAfectacionSeeder::class,
            ProductoSeeder::class,
            ProductoListaPrecioSeeder::class,
            PedidoSeeder::class,
            ProveedorSeeder::class,
            PadronSeeder::class,
            SerieSeeder::class,
            AlmacenSeeder::class,
            TipoMovimientoSeeder::class,
        ]);

        $sede = FSede::first();
        if ($sede) {
            // Verifica si el usuario ya existe
            $testUser = User::where('email', 'test@example.com')->first();

            if (!$testUser) {
                User::factory()->create([
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'f_sede_id' => $sede->id,
                ])->assignRole('admin')->user_empleado()->create(['empleado_id' => 1, 'tipo' => 'main']);
            }

            User::factory()->create([
                'name' => 'Vendedor 1',
                'email' => 'vendedor@example.com',
                'f_sede_id' => $sede->id,
            ])->assignRole('vendedor')->user_empleado()->create(['empleado_id' => 2, 'tipo' => 'main']);

            User::factory()->create([
                'name' => 'Vendedor 2',
                'email' => 'vendedor2@example.com',
                'f_sede_id' => $sede->id,
                'deleted_at' => now(),
            ])->assignRole('vendedor')->user_empleado()->create(['empleado_id' => 3, 'tipo' => 'main']);
        } else {
            throw new \Exception('No hay sedes en la base de datos. Asegúrate de ejecutar FSedeSeeder primero.');
        }
    }
}
