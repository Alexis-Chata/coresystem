<?php

namespace App\Livewire;

use App\Models\Producto;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class KardexProducto extends Component
{
    public int $almacenId;

    // null = TODOS los productos
    public ?int $productoId = null;

    public string $fechaInicio;
    public string $fechaFin;

    public array $kardex = [];

    // Resumen por producto
    public array $resumenProductos = [];

    // Para el select
    public array $productosList = [];

    public function mount(?int $productoId = null, int $almacenId = 1)
    {
        $this->almacenId  = $almacenId;
        $this->productoId = $productoId;

        $this->fechaInicio = now()->startOfMonth()->toDateString();
        $this->fechaFin    = now()->toDateString();

        $this->productosList = Producto::withTrashed()
            ->pluck('name', 'id')
            ->toArray();

        $this->cargarKardex();
    }

    public function cargarKardex(): void
    {
        // =========================
        // 0) IDs de productos a considerar
        // =========================
        $idsAlmacen = DB::table('almacen_productos')
            ->where('almacen_id', $this->almacenId)
            ->when($this->productoId, fn($q) => $q->where('producto_id', $this->productoId))
            ->orderBy('producto_id')
            ->pluck('producto_id')
            ->unique()
            ->values()
            ->all();

        // =========================
        // 1) STOCK ACTUAL (almacen_productos está en CAJAS → convertir a PAQUETES con productos.factor)
        $stockRows = DB::table('almacen_productos as ap')
            ->join('productos as p', 'p.id', '=', 'ap.producto_id')
            ->where('ap.almacen_id', $this->almacenId)
            ->when($this->productoId, fn($q) => $q->where('ap.producto_id', $this->productoId))
            ->select('ap.producto_id', 'ap.stock_fisico', 'p.cantidad')
            ->get();

        // stockActual ya en PAQUETES
        $stockActual = [];
        $factors = [];

        foreach ($stockRows as $r) {
            $factor = (int) ($r->cantidad ?? 1);
            if ($factor <= 0) $factor = 1; // seguridad

            $factors[$r->producto_id] = $factor;

            // ap.stock_fisico está en cajas -> convertir a paquetes
            $stockActual[$r->producto_id] = convertir_a_paquetes($r->stock_fisico, $factor);
        }

        // =========================
        // 2) AJUSTE POSTERIOR AL RANGO por producto (para ir hacia atrás)
        // =========================
        $ajustePosterior = DB::table('movimiento_detalles as md')
            ->join('movimientos as m', 'm.id', '=', 'md.movimiento_id')
            ->join('tipo_movimientos as tm', 'tm.id', '=', 'm.tipo_movimiento_id')
            ->where('m.almacen_id', $this->almacenId)
            ->when($this->productoId, fn($q) => $q->where('md.producto_id', $this->productoId))
            ->where('m.fecha_movimiento', '>', $this->fechaFin)
            ->groupBy('md.producto_id')
            ->selectRaw("
                md.producto_id,
                SUM(
                    CASE
                        WHEN tm.tipo = 'ingreso' THEN md.cantidad_total_unidades * -1
                        WHEN tm.tipo = 'salida'  THEN md.cantidad_total_unidades
                    END
                ) AS ajuste
            ")
            ->pluck('ajuste', 'md.producto_id')
            ->toArray();

        // =========================
        // 3) MOVIMIENTOS DEL RANGO (TODOS los productos)
        //    IMPORTANTE: ordenar por producto y fecha DESC para reconstrucción correcta
        // =========================
        $movimientos = DB::table('movimiento_detalles as md')
            ->join('movimientos as m', 'm.id', '=', 'md.movimiento_id')
            ->join('tipo_movimientos as tm', 'tm.id', '=', 'm.tipo_movimiento_id')
            ->join('productos as p', 'p.id', '=', 'md.producto_id')
            ->where('m.almacen_id', $this->almacenId)
            ->when($this->productoId, fn($q) => $q->where('md.producto_id', $this->productoId))
            ->whereBetween('m.fecha_movimiento', [$this->fechaInicio, $this->fechaFin])
            ->orderBy('md.producto_id')           // ✅ agrupa por producto
            ->orderByDesc('m.fecha_movimiento')   // ✅ y dentro del producto va hacia atrás
            ->orderByDesc('md.id')
            ->select([
                'md.producto_id',
                'md.factor',
                'm.fecha_movimiento',
                'tm.codigo as codigo_tipo_movimiento',
                'tm.name as movimiento',
                'tm.tipo as naturaleza', // ingreso | salida
                'md.cantidad_total_unidades',
                'p.id as producto_cod',
                'p.name as producto_name',
            ])
            ->get();

        // IDs finales (almacén + los que aparecen en movimientos)
        $idsMovs = $movimientos->pluck('producto_id')->unique()->values()->all();
        $productoIds = array_values(array_unique(array_merge($idsAlmacen, $idsMovs)));

        // =========================
        // 4) SALDO FINAL del periodo por producto
        // =========================
        $saldoFinalPorProducto = [];
        foreach ($productoIds as $pid) {
            $sa = (int) ($stockActual[$pid] ?? 0);
            $aj = (int) ($ajustePosterior[$pid] ?? 0);
            $saldoFinalPorProducto[$pid] = $sa + $aj;
        }

        // =========================
        // 5) RECONSTRUIR KARDEX (BACKWARD) por producto
        // =========================
        $saldoPorProducto = $saldoFinalPorProducto;
        $this->kardex = [];

        foreach ($movimientos as $mov) {
            $pid = (int) $mov->producto_id;

            $saldo = (int) ($saldoPorProducto[$pid] ?? 0);
            $saldoDespues = $saldo;

            if ($mov->naturaleza === 'ingreso') {
                $saldo -= (int) $mov->cantidad_total_unidades;
            } else { // salida
                $saldo += (int) $mov->cantidad_total_unidades;
            }

            $saldoPorProducto[$pid] = $saldo;

            $this->kardex[] = [
                'fecha'           => $mov->fecha_movimiento,
                'producto_id'     => $pid,
                'producto_codigo' => $mov->producto_cod,
                'producto_nombre' => $mov->producto_name,
                'codigo'          => $mov->codigo_tipo_movimiento,
                'movimiento'      => $mov->movimiento,
                'tipo'            => strtoupper($mov->naturaleza),
                'cantidad'        => $mov->cantidad_total_unidades,
                'saldo_antes'       => $saldo,
                'saldo_antes_cajas' => convertir_a_cajas($saldo, $mov->factor),
                'saldo_despues'     => $saldoDespues,
                'saldo_despues_cajas' => convertir_a_cajas($saldoDespues, $mov->factor),
            ];
        }

        // =========================
        // 6) RESUMEN (saldo inicial y final) por producto
        // =========================
        $this->resumenProductos = [];
        $productosInfo = Producto::withTrashed()
            ->whereIn('id', $productoIds)
            ->get(['id', 'name', 'cantidad']) // O como se llame tu columna de factor
            ->keyBy('id'); // Esto hace que el ID sea la llave del array

        foreach ($productoIds as $pid) {
            $producto = $productosInfo[$pid] ?? null;

            $this->resumenProductos[] = [
                'producto_id'     => $pid,
                'producto_nombre' => $producto->name ?? ('#' . $pid),
                'saldo_inicial'   => ($saldoPorProducto[$pid] ?? 0),
                'saldo_inicial_cajas'   => convertir_a_cajas($saldoPorProducto[$pid] ?? 0, $producto->cantidad ?? 1),
                'saldo_final'     => ($saldoFinalPorProducto[$pid] ?? 0),
                'saldo_final_cajas'     => convertir_a_cajas($saldoFinalPorProducto[$pid] ?? 0, $producto->cantidad ?? 1),
            ];
        }
    }

    public function render()
    {
        $this->dispatch('DataTable-initialize');
        return view('livewire.kardex-producto');
    }
}
