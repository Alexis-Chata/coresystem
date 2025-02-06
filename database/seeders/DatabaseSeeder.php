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
        // Configurar SQLite para usar el archivo temporal
        config(['database.connections.sqlite-temp' => [
            'driver' => 'sqlite',
            'database' => storage_path('app/temp_db.sqlite'),
            'prefix' => '',
        ]]);
        $this->call([
            //CsvSeeder::class,
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
            //PedidoSeeder::class,
            ProveedorSeeder::class,
            PadronSeeder::class,
            SerieSeeder::class,
            AlmacenSeeder::class,
            TipoMovimientoSeeder::class,
            AlmacenProductoSeeder::class,
        ]);
        $sede = FSede::first();
        if ($sede) {
            // Verifica si el usuario ya existe
            $testUser = User::where('email', 'alexizz.19.ac@gmail.com')->first();

            if (!$testUser) {
                User::factory()->create([
                    'name' => 'Test User',
                    'email' => 'alexizz.19.ac@gmail.com',
                    'f_sede_id' => $sede->id,
                ])->assignRole('admin')->user_empleado()->create(['empleado_id' => 10, 'tipo' => 'main']);
            }

            $vendedores = [
                [
                    'name' => "Charlie Jara Huaringa",
                    'email' => "charliejara10@gmail.com",
                    'empleado_id' => 11,
                ],
                [
                    'name' => "Roselvi Segura Herrera",
                    'email' => "roselvisegura84@gmail.com",
                    'empleado_id' => 12,
                ],
                [
                    'name' => "Paul Castelo",
                    'email' => "paulcastelo123456@gmail.com",
                    'empleado_id' => 13,
                ],
                [
                    'name' => "Cuchurrumin 3000",
                    'email' => "xelmejor.20@gmail.com",
                    'empleado_id' => 14,
                ],
                [
                    'name' => "hugo rosales",
                    'email' => "junior_23_3@hotmail.com",
                    'empleado_id' => 15,
                ],
                [
                    'name' => "Dixon",
                    'email' => "dixon.adqm@gmail.com",
                    'empleado_id' => 16,
                ],
                [
                    'name' => "Angel lopez",
                    'email' => "anday4376@gmail.com",
                    'empleado_id' => 17,
                ],
                [
                    'name' => "Paul Lizana",
                    'email' => "hancook.gold@gmail.com",
                    'empleado_id' => 18,
                ],
                [
                    'name' => "Christian Hugo Jara",
                    'email' => "cj.zoar@gmail.com",
                    'empleado_id' => 19,
                ],
                [
                    'name' => "Leao nacimento",
                    'email' => "leao.do90@gmail.com",
                    'empleado_id' => 20,
                ],
                [
                    'name' => "Angela rivas",
                    'email' => "angelarm0509@gmail.com",
                    'empleado_id' => 21,
                ],
                [
                    'name' => "Alexander Gomez Pillaca",
                    'email' => "agomezpill@gmail.com",
                    'empleado_id' => 22,
                ],
                [
                    'name' => "Hugo Campos",
                    'email' => "hugomayocampos@gmail.com",
                    'empleado_id' => 23,
                ],
                [
                    'name' => "Wuilliams",
                    'email' => "zambranowulliams@gmail.com",
                    'empleado_id' => 24,
                ],
                [
                    'name' => "Jhonatan elyt",
                    'email' => "zunigadiosesjhonatanelyt@gmail.com",
                    'empleado_id' => 25,
                ],
                [
                    'name' => "Juan Manuel Macavilca Cangalaya",
                    'email' => "alone0220_m@hotmail.com",
                    'empleado_id' => 26,
                ],
                [
                    'name' => "Luis",
                    'email' => "sanchezhuanccollucho@gmail.com",
                    'empleado_id' => 27,
                ],
                [
                    'name' => "Rolando",
                    'email' => "vendedor19.golomix@gmail.com",
                    'empleado_id' => 28,
                ],
                [
                    'name' => "N.lo",
                    'email' => "icabotnlo@gmail.com",
                    'empleado_id' => 29,
                ],
                [
                    'name' => "Jhon Benites",
                    'email' => "07benitesjhon@gmail.com",
                    'empleado_id' => 31,
                ],
            ];
            foreach ($vendedores as $vendedor) {
                User::factory()->create([
                    'name' => $vendedor['name'],
                    'email' => $vendedor['email'],
                    'f_sede_id' => $sede->id,
                ])->assignRole('vendedor')->user_empleado()->create(['empleado_id' => $vendedor['empleado_id'], 'tipo' => 'main']);
            }

            // User::factory()->create([
            //     'name' => 'Vendedor 1',
            //     'email' => 'vendedor@example.com',
            //     'f_sede_id' => $sede->id,
            // ])->assignRole('vendedor')->user_empleado()->create(['empleado_id' => 11, 'tipo' => 'main']);

            // User::factory()->create([
            //     'name' => 'Vendedor 2',
            //     'email' => 'vendedor2@example.com',
            //     'f_sede_id' => $sede->id,
            //     'deleted_at' => now(),
            // ])->assignRole('vendedor')->user_empleado()->create(['empleado_id' => 12, 'tipo' => 'main']);

        } else {
            throw new \Exception('No hay sedes en la base de datos. Asegúrate de ejecutar FSedeSeeder primero.');
        }
    }
}
