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

        $ruta = Ruta::first();
        if (!$ruta) {
            throw new \Exception('No hay rutas en la base de datos. Asegúrate de ejecutar RutaSeeder primero.');
        }

        $clientes = [
            [
                'razon_social' => 'Empresa A S.A.C.',
                'direccion' => 'Av. Principal 123, Lima',
                'numero_documento' => '20123456789',
                'celular' => '987654321',
            ],
            [
                'razon_social' => 'Comercial B E.I.R.L.',
                'direccion' => 'Jr. Comercio 456, Lima',
                'numero_documento' => '20987654321',
                'celular' => '123456789',
            ],
            // Añade más clientes según sea necesario
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
                'ruta_id' => $ruta->id,
            ]);
        }
    }
}
