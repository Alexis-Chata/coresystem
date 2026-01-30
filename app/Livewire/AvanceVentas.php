<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AvanceVentas extends Component
{
    public string $desde;
    public string $hasta;

    // 'ALL' para admin, o id de empleado para vendedor
    public string $vendedorFiltro = 'ALL';

    public bool $isAdmin = false;

    public function mount(): void
    {
        $this->desde = Carbon::now()->startOfMonth()->toDateString();
        $this->hasta = Carbon::now()->toDateString();

        $this->isAdmin = $this->checkIsAdmin();

        // Si NO es admin, amarramos al empleado del usuario
        if (! $this->isAdmin) {
            $empId = $this->getEmpleadoIdDelUsuario();
            if ($empId) {
                $this->vendedorFiltro = (string) $empId;
            }
        }
    }

    private function checkIsAdmin(): bool
    {
        $role = Role::findOrCreate('admin');
        Permission::firstOrCreate(['name' => 'view avance'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'admin avance'])->assignRole($role);
        $user = auth()->user();

        return $user && $user->can('admin avance');
    }

    private function getEmpleadoIdDelUsuario(): ?int
    {
        $userId = auth()->id();

        $empId = DB::table('user_empleados')
            ->where('user_id', $userId)
            ->whereIn('tipo', ['vendedor', 'Vendedor', 'main', 'principal'])
            ->value('empleado_id');

        if (! $empId) {
            $empId = DB::table('user_empleados')
                ->where('user_id', $userId)
                ->value('empleado_id');
        }

        return $empId ? (int) $empId : null;
    }

    private function normalizeRangoFechas(): void
    {
        if ($this->desde > $this->hasta) {
            [$this->desde, $this->hasta] = [$this->hasta, $this->desde];
        }
    }

    /** ====== QUERIES BASE ====== */

    private function baseQueryCab()
    {
        $this->normalizeRangoFechas();

        return DB::table('f_comprobante_sunats as f')
            ->leftJoin('empleados as e', DB::raw('e.id'), '=', DB::raw('CAST(f.vendedor_id AS UNSIGNED)'))
            ->whereBetween('f.pedido_fecha_factuacion', [
                $this->desde . ' 00:00:00',
                $this->hasta . ' 23:59:59',
            ])
            ->where('f.estado_reporte', 1);
    }

    private function baseQueryDet()
    {
        $this->normalizeRangoFechas();

        return DB::table('f_comprobante_sunat_detalles as d')
            ->join('f_comprobante_sunats as f', 'f.id', '=', 'd.f_comprobante_sunat_id')
            ->whereBetween('f.pedido_fecha_factuacion', [
                $this->desde . ' 00:00:00',
                $this->hasta . ' 23:59:59',
            ])
            ->where('f.estado_reporte', 1);
    }

    /** ====== KPIs ====== */

    /** ====== KPIs ====== */
    private function getKpis()
    {
        $qCab = $this->baseQueryCab();

        // El filtro de vendedor solo afecta KPIs
        if ($this->vendedorFiltro !== 'ALL') {
            $qCab->whereRaw('CAST(f.vendedor_id AS UNSIGNED) = ?', [(int) $this->vendedorFiltro]);
        }

        return (clone $qCab)->selectRaw("
        COALESCE(SUM(CAST(f.mtoImpVenta AS DECIMAL(15,2))), 0) AS total_ventas,
        COUNT(DISTINCT f.cliente_id) AS clientes_unicos,
        COALESCE(AVG(CAST(f.mtoImpVenta AS DECIMAL(15,2))), 0) AS ticket_prom
    ")->first();
    }


    /** ====== RANKING POR VENDEDOR (TOTAL) ====== */

    private function getRankingVendedores()
    {
        $q = $this->baseQueryCab();

        if ($this->vendedorFiltro !== 'ALL') {
            $q->whereRaw('CAST(f.vendedor_id AS UNSIGNED) = ?', [(int) $this->vendedorFiltro]);
        }

        return $q->selectRaw("
            CAST(f.vendedor_id AS UNSIGNED) AS cod_prevendedor,
            COALESCE(e.name, CONCAT('Vendedor ', f.vendedor_id)) AS vendedor,
            COALESCE(SUM(CAST(f.mtoImpVenta AS DECIMAL(15,2))), 0) AS total_ventas,
            COUNT(DISTINCT f.cliente_id) AS clientes_unicos
        ")
            ->groupBy(
                DB::raw('CAST(f.vendedor_id AS UNSIGNED)'),
                DB::raw("COALESCE(e.name, CONCAT('Vendedor ', f.vendedor_id))")
            )
            ->orderByDesc('total_ventas')
            ->get();
    }

    /** ====== RANKING POR VENDEDOR + MARCA ====== */

    private function getRankingVendedorMarca()
    {
        $q = DB::table('f_comprobante_sunat_detalles as d')
            ->join('f_comprobante_sunats as f', 'f.id', '=', 'd.f_comprobante_sunat_id')
            ->leftJoin('empleados as e', DB::raw('e.id'), '=', DB::raw('CAST(f.vendedor_id AS UNSIGNED)'))
            ->leftJoin('productos as p', DB::raw('p.id'), '=', DB::raw('CAST(d.codProducto AS UNSIGNED)'))
            ->leftJoin('marcas as m', 'm.id', '=', 'p.marca_id')
            ->whereBetween('f.pedido_fecha_factuacion', [
                $this->desde . ' 00:00:00',
                $this->hasta . ' 23:59:59',
            ])
            ->where('f.estado_reporte', 1);

        if ($this->vendedorFiltro !== 'ALL') {
            $q->whereRaw('CAST(f.vendedor_id AS UNSIGNED) = ?', [(int) $this->vendedorFiltro]);
        }

        return $q->selectRaw("
            CAST(f.vendedor_id AS UNSIGNED) AS cod_prevendedor,
            COALESCE(e.name, CONCAT('Vendedor ', f.vendedor_id)) AS vendedor,
            COALESCE(m.name, 'SIN MARCA') AS marca,
            COALESCE(SUM(
                CAST(REPLACE(d.cantidad, ',', '.') AS DECIMAL(15,3)) *
                CAST(REPLACE(d.mtoPrecioUnitario, ',', '.') AS DECIMAL(15,3))
            ), 0) AS total_ventas,
            COUNT(DISTINCT f.cliente_id) AS clientes_unicos
        ")
            ->groupBy(
                DB::raw('CAST(f.vendedor_id AS UNSIGNED)'),
                DB::raw("COALESCE(e.name, CONCAT('Vendedor ', f.vendedor_id))"),
                DB::raw("COALESCE(m.name, 'SIN MARCA')")
            )
            ->orderBy('vendedor')
            ->orderBy('marca')
            ->get();
    }

    private function getVendedoresSelect()
    {
        return $this->baseQueryCab()
            ->selectRaw("
            DISTINCT CAST(f.vendedor_id AS UNSIGNED) AS cod_prevendedor,
            COALESCE(e.name, CONCAT('Vendedor ', f.vendedor_id)) AS vendedor
        ")
            ->orderBy('vendedor')
            ->get();
    }

    private function getVentasPorListaPrecio()
    {
        $q = $this->baseQueryDet();

        if ($this->vendedorFiltro !== 'ALL') {
            $q->whereRaw('CAST(f.vendedor_id AS UNSIGNED) = ?', [(int) $this->vendedorFiltro]);
        }

        $q->leftJoin(
            'lista_precios as lp',
            DB::raw('lp.id'),
            '=',
            DB::raw('CAST(d.ref_producto_lista_precio AS UNSIGNED)')
        );

        $listaIdExpr = "COALESCE(CAST(NULLIF(d.ref_producto_lista_precio,'') AS UNSIGNED), 0)";

        $listaNameExpr = "
        CASE
            WHEN d.ref_producto_lista_precio IS NULL OR d.ref_producto_lista_precio = '' THEN 'SIN LISTA'
            ELSE COALESCE(lp.name, CONCAT('Lista ', d.ref_producto_lista_precio))
        END
    ";

        return $q->selectRaw("
        {$listaIdExpr} AS lista_id,
        {$listaNameExpr} AS lista_precio,

        COALESCE(SUM(
            CAST(REPLACE(d.cantidad, ',', '.') AS DECIMAL(15,3)) *
            CAST(REPLACE(d.mtoPrecioUnitario, ',', '.') AS DECIMAL(15,3))
        ), 0) AS total_ventas,

        COUNT(DISTINCT f.id) AS documentos,
        COUNT(DISTINCT f.cliente_id) AS clientes_unicos
    ")
            ->groupByRaw("{$listaIdExpr}")
            ->groupByRaw("{$listaNameExpr}")
            ->orderByDesc('total_ventas')
            ->get();
    }


    public function render()
    {
        return view('livewire.avance-ventas', [
            'kpis'             => $this->getKpis(),
            'ranking'          => $this->getRankingVendedores(),
            'rankingMarca'     => $this->getRankingVendedorMarca(),
            'ventasListaPrecio' => $this->getVentasPorListaPrecio(),
            'vendedores'       => $this->getVendedoresSelect(),
        ]);
    }
}
