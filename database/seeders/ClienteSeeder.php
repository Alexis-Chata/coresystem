<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;
use App\Models\F_tipo_documento;
use App\Models\Empresa;
use App\Models\Lista_precio;
use App\Models\Ruta;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::first();
        if (!$empresa) {
            throw new \Exception('No hay empresas en la base de datos. Asegúrate de ejecutar EmpresaSeeder primero.');
        }

        $tipoDocumento = F_tipo_documento::where('tipo_documento', 'RUC')->first();
        if (!$tipoDocumento) {
            // Si no existe, créalo
            $tipoDocumento = F_tipo_documento::create([
                'tipo_documento' => 'RUC',
                'name' => 'Registro Único de Contribuyentes'
            ]);
        }

        $listaPrecio = Lista_precio::first();
        if (!$listaPrecio) {
            throw new \Exception('No hay listas de precios en la base de datos. Asegúrate de ejecutar ListaPrecioSeeder primero.');
        }

        $rutas = Ruta::all();
        foreach ($rutas as $ruta) {
            Cliente::factory()->count(25)->create([
                'f_tipo_documento_id' => $tipoDocumento->id,
                'empresa_id' => $empresa->id,
                'ruta_id' => $ruta->id,
                'lista_precio_id' => $ruta->lista_precio_id,
            ]);
        }

        $clientes = [
            [
                'razon_social' => 'Comercial San Miguel S.A.C.',
                'direccion' => 'Av. La Marina 2345, San Miguel, Lima',
                'numero_documento' => '20547896321',
                'celular' => '951234567',
            ],
            [
                'razon_social' => 'Distribuidora Los Andes E.I.R.L.',
                'direccion' => 'Jr. Huallaga 456, Cercado de Lima',
                'numero_documento' => '20563412789',
                'celular' => '962345678',
            ],
            [
                'razon_social' => 'Inversiones Miraflores S.A.C.',
                'direccion' => 'Av. Larco 785, Miraflores, Lima',
                'numero_documento' => '20587463215',
                'celular' => '973456789',
            ],
            [
                'razon_social' => 'Corporación Surco S.R.L.',
                'direccion' => 'Av. Benavides 1234, Santiago de Surco, Lima',
                'numero_documento' => '20596321478',
                'celular' => '984567890',
            ],
            [
                'razon_social' => 'Importaciones Del Norte S.A.C.',
                'direccion' => 'Av. José Pardo 567, Chimbote, Ancash',
                'numero_documento' => '20512378964',
                'celular' => '995678901',
            ],
            [
                'razon_social' => 'Distribuidora Cusco Imperial E.I.R.L.',
                'direccion' => 'Av. El Sol 789, Cusco',
                'numero_documento' => '20523697841',
                'celular' => '916789012',
            ],
            [
                'razon_social' => 'Comercializadora Arequipa S.A.C.',
                'direccion' => 'Av. Ejercito 1523, Arequipa',
                'numero_documento' => '20534789621',
                'celular' => '927890123',
            ],
            [
                'razon_social' => 'Negocios Trujillo S.R.L.',
                'direccion' => 'Jr. Pizarro 856, Trujillo, La Libertad',
                'numero_documento' => '20545632147',
                'celular' => '938901234',
            ],
            [
                'razon_social' => 'Inversiones Piura S.A.C.',
                'direccion' => 'Av. Grau 432, Piura',
                'numero_documento' => '20556987412',
                'celular' => '949012345',
            ],
            [
                'razon_social' => 'Distribuidora Tacna E.I.R.L.',
                'direccion' => 'Av. Bolognesi 678, Tacna',
                'numero_documento' => '20567894563',
                'celular' => '960123456',
            ],
            [
                'razon_social' => 'Comercial Ica S.A.C.',
                'direccion' => 'Av. San Martín 234, Ica',
                'numero_documento' => '20578963214',
                'celular' => '971234567',
            ],
            [
                'razon_social' => 'Corporación Huancayo S.R.L.',
                'direccion' => 'Jr. Arequipa 567, Huancayo, Junín',
                'numero_documento' => '20589632147',
                'celular' => '982345678',
            ],
            [
                'razon_social' => 'Negocios Chiclayo E.I.R.L.',
                'direccion' => 'Av. Balta 890, Chiclayo, Lambayeque',
                'numero_documento' => '20590123456',
                'celular' => '993456789',
            ],
            [
                'razon_social' => 'Importadora Callao S.A.C.',
                'direccion' => 'Av. Sáenz Peña 345, Callao',
                'numero_documento' => '20601234567',
                'celular' => '914567890',
            ],
            [
                'razon_social' => 'Distribuidora Huánuco S.R.L.',
                'direccion' => 'Jr. Huallayco 678, Huánuco',
                'numero_documento' => '20612345678',
                'celular' => '925678901',
            ],
        ];

        foreach ($clientes as $cliente) {
            Cliente::create([
                'razon_social' => $cliente['razon_social'],
                'direccion' => $cliente['direccion'],
                'f_tipo_documento_id' => $tipoDocumento->id,
                'numero_documento' => $cliente['numero_documento'],
                'celular' => $cliente['celular'],
                'empresa_id' => $empresa->id,
                'lista_precio_id' => $listaPrecio->id,
                'ruta_id' => Ruta::inRandomOrder()->first()->id,
            ]);
        }
    }
}
