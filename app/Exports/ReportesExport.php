<?php

namespace App\Exports;

use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportesExport implements FromCollection, WithHeadings
{
    private $date_field;
    private $fecha_inicio;
    private $fecha_fin;
    private $ruta_id;
    private $marca_id;
    private $marcas_name;
    private $vendedor_id;
    private $producto_id;

    private $ruta;
    private $tipo_documento;
    private $conductor;
    private $vendedor;
    private $cliente;
    private $num_documento;
    private $producto;
    private $fecha_emision;
    private $usuario;

    public function __construct($date_field, $fecha_inicio, $fecha_fin, $ruta_id = null, $marca = null, $vendedor_id = null, $producto_id = null, $ruta = false, $marcas_name = false, $tipo_documento = false, $conductor = false, $vendedor = false, $cliente = false, $num_documento = false, $producto = false, $fecha_emision = false, $usuario = false, $producto_factor = false)
    {
        $this->date_field = $date_field;
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin = $fecha_fin;
        $this->ruta_id = $ruta_id;
        $this->marca_id = $marca;
        $this->vendedor_id = $vendedor_id;
        $this->producto_id = $producto_id;

        $this->ruta = $ruta || !is_null($ruta_id);
        $this->marcas_name = ($marcas_name || !($this->ruta || $tipo_documento || $conductor || $vendedor || $cliente || $num_documento || $fecha_emision)) || !is_null($marca);
        $this->tipo_documento = $tipo_documento;
        $this->conductor = $conductor;
        $this->vendedor = $vendedor || !is_null($vendedor_id);
        $this->cliente = $cliente;
        $this->num_documento = $num_documento;
        $this->producto = $producto || !is_null($producto_id) || $producto_factor;
        $this->fecha_emision = $fecha_emision;
        $this->usuario = $usuario;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $ruta_id = $this->ruta_id;
        $marca_id = $this->marca_id;
        $vendedor_id = $this->vendedor_id;
        $producto_id = $this->producto_id;

        $ruta = $this->ruta;
        $marcas_name = $this->marcas_name;
        $tipo_documento = $this->tipo_documento;
        $conductor = $this->conductor;
        $vendedor = $this->vendedor;
        $cliente = $this->cliente;
        $num_documento = $this->num_documento;
        $productoFlag  = $this->producto; // renombrado para no chocar
        $fecha_emision = $this->fecha_emision;
        $usuario = $this->usuario;
        //ini_set('memory_limit', '512M');
        $productos = Producto::withTrashed()->get();

        $collect_by = collect([
            ["by" => "f_comprobante_sunats.ruta_id", "estado" => $ruta],
            ["by" => "pedidos.user_id", "estado" => $usuario],
            ["by" => "users.name", "estado" => $usuario],
            ["by" => "f_comprobante_sunats.tipoDoc", "estado" => $tipo_documento],
            ["by" => "f_comprobante_sunats.conductor_id", "estado" => $conductor],
            ["by" => "marcas.name", "estado" => $marcas_name],
            ["by" => "f_comprobante_sunats.vendedor_id", "estado" => $vendedor],
            ["by" => "empleados.name", "estado" => $vendedor],
            ["by" => "f_comprobante_sunats.cliente_id", "estado" => $cliente],
            ["by" => "f_comprobante_sunats.clientRazonSocial", "estado" => $cliente],
            ["by" => DB::raw("CONCAT(f_comprobante_sunats.serie, '-', f_comprobante_sunats.correlativo)"), "estado" => $num_documento],
            ["by" => "f_comprobante_sunat_detalles.codProducto", "estado" => $productoFlag],
            ["by" => "productos.name", "estado" => $productoFlag],
            ["by" => "f_comprobante_sunat_detalles.ref_producto_cantidad_cajon", "estado" => $productoFlag],
            ["by" => DB::raw("DATE_FORMAT(f_comprobante_sunats.fechaEmision, '%d-%m-%Y')"), "estado" => $fecha_emision],
        ]);

        $array_by = $collect_by->where('estado', true)->pluck('by')->toArray();

        // blinda campo fecha permitido
        $date_field = in_array($this->date_field, [
            'f_comprobante_sunats.pedido_fecha_factuacion',
            'f_comprobante_sunats.fechaEmision',
        ]) ? $this->date_field : 'f_comprobante_sunats.fechaEmision';

        $date_inicio = Carbon::parse($this->fecha_inicio)->startOfDay(); // 2025-11-17 00:00:00
        $date_fin    = Carbon::parse($this->fecha_fin)->endOfDay();      // 2025-11-17 23:59:59

        $needProductos = $productoFlag || $marcas_name || !is_null($marca_id);
        $needMarcas    = $marcas_name;
        $needEmpleados = $vendedor;   // solo si vas a mostrar empleados.name
        $needRutas     = $ruta;       // solo si vas a mostrar rutas.name
        $needUsuario   = $usuario;    // solo si vas a mostrar users/pedidos

        $t0 = microtime(true);
        $query = DB::table('f_comprobante_sunat_detalles')
            ->join('f_comprobante_sunats', 'f_comprobante_sunat_detalles.f_comprobante_sunat_id', '=', 'f_comprobante_sunats.id')
            ->when(
                $needProductos,
                fn($q) =>
                $q->join('productos', 'f_comprobante_sunat_detalles.codProducto', '=', 'productos.id')
            )
            ->when(
                $needMarcas,
                fn($q) =>
                $q->join('marcas', 'productos.marca_id', '=', 'marcas.id')
            )
            ->when(
                $needEmpleados,
                fn($q) =>
                $q->join('empleados', 'f_comprobante_sunats.vendedor_id', '=', 'empleados.id')
            )
            ->when(
                $needRutas,
                fn($q) =>
                $q->join('rutas', 'f_comprobante_sunats.ruta_id', '=', 'rutas.id')
            )
            ->when(
                $needUsuario,
                fn($q) =>
                $q->join('pedidos', 'f_comprobante_sunats.pedido_id', '=', 'pedidos.id')
                    ->join('users', 'pedidos.user_id', '=', 'users.id')
            )
            // columnas opcionales
            ->when($ruta, fn($q) => $q->addSelect('f_comprobante_sunats.ruta_id', 'rutas.name as rutas_name'))
            ->when($usuario, fn($q) => $q->addSelect('pedidos.user_id', 'users.name as nombre_usuario'))
            ->when($tipo_documento, fn($q) => $q->addSelect('f_comprobante_sunats.tipoDoc'))
            ->when($conductor, fn($q) => $q->addSelect('f_comprobante_sunats.conductor_id'))
            ->when($marcas_name, fn($q) => $q->addSelect('marcas.name'))
            ->when($vendedor, fn($q) => $q->addSelect('f_comprobante_sunats.vendedor_id', 'empleados.name as nombre_vendedor'))
            ->when($cliente, fn($q) => $q->addSelect('f_comprobante_sunats.cliente_id', 'f_comprobante_sunats.clientRazonSocial'))
            ->when($num_documento, fn($q) => $q->addSelect(DB::raw("CONCAT(f_comprobante_sunats.serie, '-', f_comprobante_sunats.correlativo) as num_documento")))
            ->when($productoFlag, fn($q) => $q->addSelect('f_comprobante_sunat_detalles.codProducto', 'productos.name as nombre_articulo'))
            ->when($productoFlag, fn($q) => $q->addSelect('f_comprobante_sunat_detalles.ref_producto_cantidad_cajon'))
            ->when($fecha_emision, fn($q) => $q->addSelect(DB::raw("DATE_FORMAT(f_comprobante_sunats.fechaEmision, '%d-%m-%Y') as fecha_emision")))
            ->when($productoFlag, fn($q) => $q->addSelect(DB::raw('SUM(f_comprobante_sunat_detalles.cantidad) as detalle_cantidad')))
            ->addSelect(DB::raw('ROUND(SUM(f_comprobante_sunat_detalles.cantidad * f_comprobante_sunat_detalles.mtoPrecioUnitario), 2) AS importe'))

            // filtros
            ->where('f_comprobante_sunats.estado_reporte', true)
            ->whereBetween($date_field, [$date_inicio, $date_fin])
            ->when(!is_null($ruta_id),  fn($q) => $q->where('f_comprobante_sunats.ruta_id', $ruta_id))
            ->when(!is_null($marca_id), fn($q) => $q->where('productos.marca_id', $marca_id))
            ->when(!is_null($vendedor_id), fn($q) => $q->where('f_comprobante_sunats.vendedor_id', $vendedor_id))
            ->when(!is_null($producto_id), fn($q) => $q->where('f_comprobante_sunat_detalles.codProducto', $producto_id))

            // group by dinámico solo si hay campos
            ->when(!empty($array_by), fn($q) => $q->groupBy($array_by))

            // ordenamientos coherentes con los selects
            ->when($ruta, fn($q) => $q->orderBy('f_comprobante_sunats.ruta_id'))
            ->when($usuario, fn($q) => $q->orderBy('pedidos.user_id'))
            ->when($tipo_documento, fn($q) => $q->orderBy('f_comprobante_sunats.tipoDoc'))
            ->when($conductor, fn($q) => $q->orderBy('f_comprobante_sunats.conductor_id'))
            ->when($marcas_name, fn($q) => $q->orderBy('marcas.name'))
            ->when($vendedor, fn($q) => $q->orderBy('f_comprobante_sunats.vendedor_id'))
            ->when($vendedor, fn($q) => $q->orderBy('empleados.name'))
            ->when($cliente, fn($q) => $q->orderBy('f_comprobante_sunats.cliente_id'))
            ->when($cliente, fn($q) => $q->orderBy('f_comprobante_sunats.clientRazonSocial'))
            ->when($num_documento, fn($q) => $q->orderBy('num_documento'))
            ->when($productoFlag, fn($q) => $q->orderBy('f_comprobante_sunat_detalles.codProducto'))
            ->when($productoFlag, fn($q) => $q->orderBy('productos.name'))
            ->when($productoFlag, fn($q) => $q->orderBy('f_comprobante_sunat_detalles.ref_producto_cantidad_cajon'))
            ->when($fecha_emision, fn($q) => $q->orderBy('fecha_emision'));

        // 👀 Ver SQL con bindings separados
        logger('SQL Query', [$query->toSql()]);
        logger('SQL Query getBindings', [$query->getBindings()]);
        $countQuery = (clone $query)->reorder();

        $total = DB::query()
            ->fromSub($countQuery, 't')
            ->count();

        logger("Filas a exportar: $total");

        $reporte = $query->get();

        $ms = round((microtime(true) - $t0) * 1000, 2);
        logger("ARMADO QUERY => {$ms}ms");

        // Post-proceso de cantidades a "cajas.unidades"
        if ($productoFlag) {
            // Opcional: indexar productos por id para no hacer find() en cada vuelta
            $productosById = $productos->keyBy('id');
            $reporte = $reporte->map(function ($item) use ($productosById) {
                $item->cantidad_unidades = $item->detalle_cantidad;
                $item->importe = $item->importe == 0.00 ? "0" : $item->importe;

                $prod = $productosById->get($item->codProducto);
                if ($prod && (int) $prod->cantidad > 0) {
                    $item->detalle_cantidad = convertir_a_cajas($item->detalle_cantidad, $prod->cantidad);
                }
                return $item;
            });
        }

        return $reporte;
    }

    public function headings(): array
    {
        $fecha_inicio = $this->fecha_inicio ? date("d-m-Y", strtotime($this->fecha_inicio)) : '';
        $fecha_fin = $this->fecha_fin ? date("d-m-Y", strtotime($this->fecha_fin)) : '';

        $titulos = collect([
            ["titulo" => "Ruta Cod", "estado" => $this->ruta],
            ["titulo" => "Descrip Ruta", "estado" => $this->ruta],
            ["titulo" => "Cod Usuario", "estado" => $this->usuario],
            ["titulo" => "Nombre Usuario", "estado" => $this->usuario],
            ["titulo" => "Tipo Doc", "estado" => $this->tipo_documento],
            ["titulo" => "Conductor", "estado" => $this->conductor],
            ["titulo" => "Descrip Marca", "estado" => $this->marcas_name],
            ["titulo" => "Cod Prevendedor", "estado" => $this->vendedor],
            ["titulo" => "Nombre Prevendedor", "estado" => $this->vendedor],
            ["titulo" => "Cod Cliente", "estado" => $this->cliente],
            ["titulo" => "Nombre Cliente", "estado" => $this->cliente],
            ["titulo" => "Num Documento", "estado" => $this->num_documento],
            ["titulo" => "Cod Articulo", "estado" => $this->producto],
            ["titulo" => "Nombre de Articulo", "estado" => $this->producto],
            ["titulo" => "Factor", "estado" => $this->producto],
            ["titulo" => "Fecha Emision", "estado" => $this->fecha_emision],
            ["titulo" => "Cantidad Bultos Venta", "estado" => $this->producto],
            ["titulo" => "Importe Venta", "estado" => true], // siempre incluido
            ["titulo" => "Cantidad Unidades Venta", "estado" => $this->producto],
        ]);

        $titulos_array = $titulos
            ->where('estado', true) // más corto que filter(fn...)
            ->pluck('titulo')       // más corto que map(fn...)
            ->toArray();

        return [
            ["Reporte de Ventas"],
            ["Fecha Del : $fecha_inicio AL: $fecha_fin"],
            $titulos_array,
        ];
    }
}
