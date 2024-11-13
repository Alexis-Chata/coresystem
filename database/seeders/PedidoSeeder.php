<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pedido;
use App\Models\Ruta;
use App\Models\FTipoComprobante;
use App\Models\Empleado;
use App\Models\Cliente;
use App\Models\Empresa;

class PedidoSeeder extends Seeder
{
    public function run(): void
    {
        $rutas = Ruta::all();
        $tiposComprobante = FTipoComprobante::all();
        $vendedores = Empleado::where('tipo_empleado', 'vendedor')->get();
        $conductores = Empleado::where('tipo_empleado', 'conductor')->get();
        $clientes = Cliente::all();
        $empresa = Empresa::first();

        if ($rutas->isEmpty() || $tiposComprobante->isEmpty() || $vendedores->isEmpty() ||
            $conductores->isEmpty() || $clientes->isEmpty() || !$empresa) {
            throw new \Exception('Asegúrate de que existan rutas, tipos de comprobante, vendedores, conductores, clientes y una empresa.');
        }

        for ($i = 0; $i < 10; $i++) {
            Pedido::create([
                'ruta_id' => $rutas->random()->id,
                'f_tipo_comprobante_id' => $tiposComprobante->random()->id,
                'vendedor_id' => $vendedores->random()->id,
                'conductor_id' => $conductores->random()->id,
                'cliente_id' => $clientes->random()->id,
                'fecha_emision' => now()->subDays(rand(1, 30))->format('Y-m-d'),
                'importe_total' => rand(100, 1000) . '.' . rand(0, 99),
                'nro_doc_liquidacion' => 'LIQ-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'lista_precio' => 'Lista ' . rand(1, 3),
                'empresa_id' => $empresa->id,
            ]);
        }
    }
}
