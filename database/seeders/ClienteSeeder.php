<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;
use App\Models\FTipoDocumento;
use App\Models\Empresa;
use App\Models\ListaPrecio;
use App\Models\Padron;
use App\Models\Ruta;
use Illuminate\Support\Facades\DB;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::first();
        if (!$empresa) {
            throw new \Exception('No hay empresas en la base de datos. Asegúrate de ejecutar EmpresaSeeder primero.');
        }

        $tipoDocumento = FTipoDocumento::where('tipo_documento', 'RUC')->first();
        if (!$tipoDocumento) {
            // Si no existe, créalo
            $tipoDocumento = FTipoDocumento::create([
                'tipo_documento' => 'RUC',
                'name' => 'Registro Único de Contribuyentes'
            ]);
        }

        $listaPrecio = ListaPrecio::first();
        if (!$listaPrecio) {
            throw new \Exception('No hay listas de precios en la base de datos. Asegúrate de ejecutar ListaPrecioSeeder primero.');
        }
        $inicio = microtime(true);
        $rutas = Ruta::all();
        DB::setDefaultConnection('sqlite-temp');
        // Realizar consultas (ejemplo)
        $rutas_temporales = DB::table('rutas_temporales')->whereNot('dia_visita', '-')->get();
        $cruts = $rutas_temporales->pluck('crut')->map(function ($crut) {
            return str_pad($crut, 3, '0', STR_PAD_LEFT); // Aplica el padding
        })->toArray();
        //print_r($cruts);
        $padrones_temporales = DB::table('padrones_temporales')->whereIn('crut', $cruts)->orderBy('nsecprev', 'asc')->orderBy('ccli', 'asc')->get();
        $clientes_temporales = DB::table('clientes_temporales')->get();
        DB::setDefaultConnection('mysql');
        $tipo_doc = FTipoDocumento::all();

        foreach ($padrones_temporales as $padron) {

            $cliente_temp = $clientes_temporales->first(function ($item) use ($padron) {
                return $item->ccli == $padron->ccli;
            });

            $ruta_temp = $rutas_temporales->first(function ($item) use ($padron) {
                return $item->crut == $padron->crut;
            });
            $ruta = $rutas->find($ruta_temp->nuevo_id);
            //print_r($ruta->lista_precio_id);

            $f_tipo_documento_id = $tipo_doc->where('tipo_documento', 'DNI')->first()->id;
            $numero_documento = 99999999;
            $celular = null;
            if (!empty($cliente_temp->le)) {
                $numero_documento = $cliente_temp->le;
            }
            if (!empty($cliente_temp->cruc)) {
                $f_tipo_documento_id = $tipo_doc->where('tipo_documento', 'RUC')->first()->id;
                $numero_documento = $cliente_temp->cruc;
            }
            if (!empty($cliente_temp->ntel) and strlen($cliente_temp->ntel) == 9) {
                $celular = $cliente_temp->ntel;
            }
            if (!empty($cliente_temp->nfax) and strlen($cliente_temp->nfax) == 9) {
                $celular = $cliente_temp->nfax;
            }

            $cliente = Cliente::create([
                'razon_social' => $cliente_temp->tcli,
                'direccion' => $cliente_temp->tdir,
                'f_tipo_documento_id' => $f_tipo_documento_id,
                'numero_documento' => $numero_documento,
                'celular' => $celular,
                'empresa_id' => 1,
                'lista_precio_id' => $ruta->lista_precio_id,
                'ruta_id' => $ruta->id,
            ]);
            //break;
        }
        DB::setDefaultConnection('mysql');
        $fin = microtime(true);
        $tiempo = $fin - $inicio;
        echo "    El tiempo de ejecución fue: {$tiempo} segundos.";
        echo "\n";
    }
}
