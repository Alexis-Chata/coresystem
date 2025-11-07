<?php

namespace App\Imports;

use App\Models\Cliente;
use App\Models\Padron;
use App\Models\Ruta;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ClientesImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Limpiar valores (elimina \x00, espacios extra, etc.)
        $clean = collect($row)->map(function ($value) {
            if (is_string($value)) {
                //return trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $value));
                return trim(preg_replace('/[\x00-\x1F\x7F]/u', '', mb_convert_encoding($value, 'UTF-8', 'auto')));
            }
            return $value;
        });

        $ruta = Ruta::firstOrCreate(
            ['name' => $clean['ruta']],
            [
                'codigo'          => $clean['ubigeo'] ?? 150132,
                'name'            => $clean['ruta'],
                'dia_visita'      => $clean['dia_visita'] ?? 'Lunes',
                'vendedor_id'     => $clean['vendedor_id'] ?? 10,
                'empresa_id'      => $clean['empresa_id'] ?? auth()->user()->fsede->empresa_id ?? 1,
                'lista_precio_id' => $clean['lista_precio_id'] ?? 1,
            ]
        );

        $clean['f_tipo_documento_id'] = 1; // DNI
        if (strlen($clean['numero_documento']) > 8) {
            $clean['f_tipo_documento_id'] = 4; // RUC
        }
        //dd($clean, $clean['ruta'], strlen($clean->get('numero_documento')), $ruta);

        $cliente = Cliente::create([
            'razon_social'        => $clean['razon_social'],
            'direccion'           => $clean['direccion'],
            'f_tipo_documento_id' => $clean['f_tipo_documento_id'],
            'numero_documento'    => $clean['numero_documento'],
            'celular'             => $clean['celular'] ?? null,
            'empresa_id'          => $ruta->empresa_id,
            'lista_precio_id'     => $ruta->lista_precio_id,
            'ruta_id'             => $ruta->id,
            'ubigeo_inei'         => $ruta->codigo ?? 150132,
            'latitude'            => $clean['latitude'] ?? null,
            'longitude'           => $clean['longitude'] ?? null,
        ]);

        $ultimo = Padron::where('ruta_id', $ruta->id)->max('nro_secuencia');
        $nro_secuencia = ($ultimo ?? 0) + 1;

        return new Padron([
            'cliente_id'      => $cliente->id,
            'ruta_id'         => $ruta->id,
            'nro_secuencia'   => $nro_secuencia,
        ]);
    }

    public function batchSize(): int
    {
        return 400;
    }

    public function chunkSize(): int
    {
        return 400;
    }
}
