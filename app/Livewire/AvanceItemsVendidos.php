<?php

namespace App\Livewire;

use App\Models\Empleado;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class AvanceItemsVendidos extends Component
{
    use WithPagination;

    public string $desde;
    public string $hasta;

    public ?int $vendedorId = null;     // solo admin puede cambiar
    public string $buscarItem = '';
    public int $porPagina = 25;

    public bool $esAdmin = false;
    public ?int $miEmpleadoId = null;

    public function mount(): void
    {
        abort_unless(
            auth()->user()->can('view avance') || auth()->user()->can('admin avance'),
            403
        );

        $this->desde = Carbon::now()->startOfMonth()->toDateString();
        $this->hasta = Carbon::now()->toDateString();

        $this->esAdmin = auth()->user()->can('admin avance');
        $this->miEmpleadoId = $this->resolveEmpleadoId(auth()->id());

        // si NO es admin, se forza al vendedor del usuario
        if (! $this->esAdmin) {
            $this->vendedorId = $this->miEmpleadoId;
        }
    }

    private function resolveEmpleadoId(int $userId): ?int
    {
        // Si manejas "tipo" en el pivot (principal, etc.), prioriza ese.
        return DB::table('user_empleados')
            ->where('user_id', $userId)
            ->orderByRaw("CASE WHEN tipo IS NOT NULL THEN 0 ELSE 1 END")
            ->value('empleado_id');
    }

    public function updated($prop): void
    {
        if (in_array($prop, ['desde', 'hasta', 'vendedorId', 'buscarItem', 'porPagina'], true)) {
            $this->resetPage();
        }

        // seguridad: aunque intente cambiar por request, si no es admin se vuelve a forzar
        if ($prop === 'vendedorId' && ! $this->esAdmin) {
            $this->vendedorId = $this->miEmpleadoId;
        }
    }

    public function getVendedoresProperty()
    {
        // Ajusta el filtro si tu sistema usa otro valor en tipo_empleado
        return Empleado::query()
            ->select('id', 'name')
            ->where('tipo_empleado', 'VENDEDOR')
            ->orderBy('name')
            ->get();
    }

    private function baseDetalleQuery()
    {
        $user = auth()->user();

        // Si tu User tiene relación sede->empresa, úsala.
        // En tu SQL: users.f_sede_id -> f_sedes.empresa_id
        $empresaId = data_get($user, 'sede.empresa_id'); // ajusta si tu relación se llama distinto

        $q = DB::table('f_comprobante_sunat_detalles as d')
            ->join('f_comprobante_sunats as c', 'c.id', '=', 'd.f_comprobante_sunat_id')
            ->where('c.estado_reporte', 1)
            ->whereNotNull('c.pedido_fecha_factuacion')
            ->whereBetween('c.pedido_fecha_factuacion', [$this->desde, $this->hasta]);

        if ($empresaId) {
            $q->where('c.empresa_id', $empresaId);
        }

        $vid = $this->esAdmin ? $this->vendedorId : $this->miEmpleadoId;
        if ($vid) {
            // vendedor_id en tu tabla es varchar, por eso casteo a string
            $q->where('c.vendedor_id', (string) $vid);
        }

        if ($this->buscarItem !== '') {
            $term = '%' . $this->buscarItem . '%';
            $q->where(function ($w) use ($term) {
                $w->where('d.descripcion', 'like', $term)
                    ->orWhere('d.codProducto', 'like', $term);
            });
        }

        return $q;
    }

    public function render()
    {
        // listado agrupado por item
        $items = $this->baseDetalleQuery()
            ->selectRaw("
                d.codProducto,
                d.descripcion,
                SUM(
                    CAST(NULLIF(d.mtoValorVenta,'') AS DECIMAL(15,2))
                + CAST(NULLIF(d.totalImpuestos,'') AS DECIMAL(15,2))
                - d.valor_devuelto
                ) AS ventas,
                COUNT(DISTINCT COALESCE(NULLIF(c.cliente_id,''), NULLIF(c.clientNumDoc,''))) AS clientes_unicos
            ")
            ->groupBy('d.codProducto', 'd.descripcion')
            ->orderByRaw("CASE WHEN TRIM(d.codProducto) REGEXP '^[0-9]+$' THEN 0 ELSE 1 END") // primero numéricos
            ->orderByRaw("CAST(TRIM(d.codProducto) AS UNSIGNED) ASC")                      // numérico asc
            ->orderBy('d.codProducto', 'asc')                                        // desempate
            ->paginate($this->porPagina);

        // totales generales (sin groupBy)
        $totales = $this->baseDetalleQuery()
            ->selectRaw("
                SUM(
                    CAST(NULLIF(d.mtoValorVenta,'') AS DECIMAL(15,2))
                + CAST(NULLIF(d.totalImpuestos,'') AS DECIMAL(15,2))
                - d.valor_devuelto
                ) AS ventas,
                COUNT(DISTINCT COALESCE(NULLIF(c.cliente_id,''), NULLIF(c.clientNumDoc,''))) AS clientes_unicos
            ")
            ->first();

        return view('livewire.avance-items-vendidos', [
            'items' => $items,
            'totales' => $totales,
            'vendedores' => $this->esAdmin ? $this->vendedores : collect(),
        ]);
    }
}
