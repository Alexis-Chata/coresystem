<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AvanceCoberturaVolumenMarca extends Component
{
    public string $dateField = 'f_comprobante_sunats.pedido_fecha_factuacion';
    public string $desde = '';
    public string $hasta = '';

    public function mount(): void
    {
        $this->desde = Carbon::now()->startOfMonth()->toDateString();
        $this->hasta = Carbon::now()->toDateString();
    }

    public function updatedDesde(): void
    {
        $this->normalizarFechas();
    }

    public function updatedHasta(): void
    {
        $this->normalizarFechas();
    }

    private function normalizarFechas(): void
    {
        if ($this->desde > $this->hasta) {
            [$this->desde, $this->hasta] = [$this->hasta, $this->desde];
        }
    }

    private function getDateField(): string
    {
        $permitidos = [
            'f_comprobante_sunats.pedido_fecha_factuacion',
            'f_comprobante_sunats.fechaEmision',
        ];

        return in_array($this->dateField, $permitidos, true)
            ? $this->dateField
            : 'f_comprobante_sunats.pedido_fecha_factuacion';
    }

    private function baseQuery()
    {
        $this->normalizarFechas();

        $dateField = $this->getDateField();
        $fechaInicio = Carbon::parse($this->desde)->startOfDay();
        $fechaFin = Carbon::parse($this->hasta)->endOfDay();

        return DB::table('f_comprobante_sunat_detalles')
            ->join(
                'f_comprobante_sunats',
                'f_comprobante_sunat_detalles.f_comprobante_sunat_id',
                '=',
                'f_comprobante_sunats.id'
            )
            ->join(
                'productos',
                DB::raw('CAST(TRIM(f_comprobante_sunat_detalles.codProducto) AS UNSIGNED)'),
                '=',
                'productos.id'
            )
            ->join(
                'marcas',
                'productos.marca_id',
                '=',
                'marcas.id'
            )
            ->join(
                'empleados',
                DB::raw('CAST(TRIM(f_comprobante_sunats.vendedor_id) AS UNSIGNED)'),
                '=',
                'empleados.id'
            )
            ->where('f_comprobante_sunats.estado_reporte', true)
            ->whereBetween($dateField, [$fechaInicio, $fechaFin]);
    }

    private function getDatosBase(): \Illuminate\Support\Collection
    {
        return $this->baseQuery()
            ->selectRaw("
            f_comprobante_sunats.vendedor_id as cod_prevendedor,
            empleados.name as vendedor,
            marcas.id as marca_id,
            marcas.name as marca,
            ROUND(SUM(f_comprobante_sunat_detalles.cantidad * f_comprobante_sunat_detalles.mtoPrecioUnitario), 2) as importe
        ")
            ->groupBy(
                'f_comprobante_sunats.vendedor_id',
                'empleados.name',
                'marcas.id',
                'marcas.name'
            )
            ->orderBy('f_comprobante_sunats.vendedor_id')
            ->orderBy('marcas.id')
            ->get();
    }

    private function getPivot(): array
    {
        $datos = $this->getDatosBase();

        $marcas = $datos
            ->map(fn($item) => [
                'id' => (int) $item->marca_id,
                'name' => $item->marca,
            ])
            ->unique('id')
            ->sortBy('id')
            ->values();

        $filas = [];
        $totalesColumnas = [];

        foreach ($marcas as $marca) {
            $totalesColumnas[$marca['name']] = 0;
        }

        foreach ($datos as $item) {
            $codigo = (string) $item->cod_prevendedor;

            if (!isset($filas[$codigo])) {
                $filas[$codigo] = [
                    'cod_prevendedor' => $item->cod_prevendedor,
                    'vendedor' => $item->vendedor,
                    'marcas' => [],
                    'total_fila' => 0,
                ];

                foreach ($marcas as $marca) {
                    $filas[$codigo]['marcas'][$marca['name']] = 0;
                }
            }

            $importe = (float) $item->importe;

            $filas[$codigo]['marcas'][$item->marca] = $importe;
            $filas[$codigo]['total_fila'] += $importe;
            $totalesColumnas[$item->marca] += $importe;
        }

        $filas = collect($filas)
            ->sortBy('cod_prevendedor')
            ->values();

        return [
            'marcas' => $marcas,
            'filas' => $filas,
            'totalesColumnas' => $totalesColumnas,
            'granTotal' => array_sum($totalesColumnas),
            'totalVendedores' => $filas->count(),
        ];
    }

    public function render()
    {
        $pivot = $this->getPivot();

        return view('livewire.avance-cobertura-volumen-marca', [
            'pivot' => $pivot,
        ]);
    }
}
