<?php

namespace App\Exports;

use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportesExport implements FromCollection, WithHeadings, ShouldAutoSize
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

    public function __construct($date_field, $fecha_inicio, $fecha_fin, $ruta_id = null, $marca = null, $vendedor_id = null, $producto_id = null, $ruta = false, $marcas_name = false, $tipo_documento = false, $conductor = false, $vendedor = false, $cliente = false, $num_documento = false, $producto = false, $fecha_emision = false)
    {
        $this->date_field = $date_field;
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin = $fecha_fin;
        $this->ruta_id = $ruta_id;
        $this->marca_id = $marca;
        $this->vendedor_id = $vendedor_id;
        $this->producto_id = $producto_id;

        $this->ruta = ($ruta or !is_null($ruta_id));
        $this->marcas_name = ($marcas_name or !($this->ruta or $tipo_documento or $conductor or $vendedor or $cliente or $num_documento or $fecha_emision)) or !is_null($marca);
        $this->tipo_documento = $tipo_documento;
        $this->conductor = $conductor;
        $this->vendedor = ($vendedor or !is_null($vendedor_id));
        $this->vendedor = ($vendedor or !is_null($vendedor_id));
        $this->cliente = $cliente;
        $this->num_documento = $num_documento;
        $this->producto = ($producto or !is_null($producto_id));
        $this->fecha_emision = $fecha_emision;
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
        $producto = $this->producto;
        $fecha_emision = $this->fecha_emision;
        //ini_set('memory_limit', '512M');
        $productos = Producto::withTrashed()->get();

        $collect_by = collect([
            ["by" => "ruta_id", "estado" => $ruta],
            ["by" => "marcas.name", "estado" => $marcas_name],
            ["by" => "f_comprobante_sunats.tipoDoc", "estado" => $tipo_documento],
            ["by" => "f_comprobante_sunats.conductor_id", "estado" => $conductor],
            ["by" => "f_comprobante_sunats.vendedor_id", "estado" => $vendedor],
            ["by" => "empleados.name", "estado" => $vendedor],
            ["by" => "f_comprobante_sunats.cliente_id", "estado" => $cliente],
            ["by" => "f_comprobante_sunats.clientRazonSocial", "estado" => $cliente],
            ["by" => "num_documento", "estado" => $num_documento],
            ["by" => "f_comprobante_sunat_detalles.codProducto", "estado" => $producto],
            ["by" => "productos.name", "estado" => $producto],
            ["by" => "fecha_emision", "estado" => $fecha_emision],
        ]);

        $array_by = $collect_by->filter(function ($item) {
            return $item['estado'];
        })->map(function ($item) {
            return $item['by'];
        })->toArray();

        //dd($this->fecha_inicio, $this->fecha_fin);
        $reporte = DB::table('f_comprobante_sunat_detalles')
            ->join('f_comprobante_sunats', 'f_comprobante_sunat_detalles.f_comprobante_sunat_id', '=', 'f_comprobante_sunats.id')
            ->join('productos', 'f_comprobante_sunat_detalles.codProducto', '=', 'productos.id')
            ->join('marcas', 'productos.marca_id', '=', 'marcas.id')
            ->join('empleados', 'f_comprobante_sunats.vendedor_id', '=', 'empleados.id')
            ->join('rutas', 'f_comprobante_sunats.ruta_id', '=', 'rutas.id')
            ->when($ruta, function ($query) {
                return $query->addSelect(
                    'f_comprobante_sunats.ruta_id',
                    'rutas.name as rutas_name',
                );
            })
            ->when($marcas_name, function ($query) {
                return $query->addSelect('marcas.name');
            })
            ->when($tipo_documento, function ($query) {
                return $query->addSelect('f_comprobante_sunats.tipoDoc');
            })
            ->when($conductor, function ($query) {
                return $query->addSelect('f_comprobante_sunats.conductor_id');
            })
            ->when($vendedor, function ($query) {
                return $query->addSelect(
                    'f_comprobante_sunats.vendedor_id',
                    'empleados.name as nombre_vendedor',
                );
            })
            ->when($cliente, function ($query) {
                return $query->addSelect(
                    'f_comprobante_sunats.cliente_id',
                    'f_comprobante_sunats.clientRazonSocial',
                );
            })
            ->when($num_documento, function ($query) {
                return $query->addSelect(DB::raw("CONCAT(f_comprobante_sunats.serie, '-', f_comprobante_sunats.correlativo) as num_documento"));
            })
            ->when($producto, function ($query) {
                return $query->addSelect('f_comprobante_sunat_detalles.codProducto', 'productos.name as nombre_articulo');
            })
            ->when($fecha_emision, function ($query) {
                return $query->addSelect(DB::raw("DATE_FORMAT(f_comprobante_sunats.fechaEmision, '%d-%m-%Y') as fecha_emision"));
            })
            ->when($producto, function ($query) {
                return $query->addSelect(DB::raw("sum(f_comprobante_sunat_detalles.cantidad) as detalle_cantidad"));
            })
            ->when(true, function ($query) {
                return $query->addSelect(DB::raw("CAST(sum(f_comprobante_sunat_detalles.cantidad * f_comprobante_sunat_detalles.mtoPrecioUnitario) AS CHAR)"));
            })
            ->when(true, function ($query) { // date_field  ( 1: pedido_fecha_factuacion, 2: fechaEmision )
                $query->whereBetween($this->date_field, [$this->fecha_inicio, $this->fecha_fin]);
            })
            ->where("estado_reporte", true)
            ->when(isset($ruta_id), function ($query) use ($ruta_id) {
                return $query->where('rutas.id', $ruta_id);
            })
            ->when(isset($marca_id), function ($query) use ($marca_id) {
                return $query->where('marcas.id', $marca_id);
            })
            ->when(isset($vendedor_id), function ($query) use ($vendedor_id) {
                return $query->where('f_comprobante_sunats.vendedor_id', $vendedor_id);
            })
            ->when(isset($producto_id), function ($query) use ($producto_id) {
                return $query->where('f_comprobante_sunat_detalles.codProducto', $producto_id);
            })
            ->when(true, function ($query) use ($array_by) {
                return $query->groupBy(
                    $array_by
                );
            })
            ->when($ruta, function ($q) {
                return $q->orderBy('f_comprobante_sunats.ruta_id');
            })
            ->when($marcas_name, function ($q) {
                return $q->orderBy('marcas.name');
            })
            ->when($tipo_documento, function ($q) {
                return $q->orderBy('f_comprobante_sunats.tipoDoc');
            })
            ->when($conductor, function ($q) {
                return $q->orderBy('f_comprobante_sunats.conductor_id');
            })
            ->when($vendedor, function ($q) {
                return $q->orderBy('f_comprobante_sunats.vendedor_id');
            })
            ->when($vendedor, function ($q) {
                return $q->orderBy('empleados.name');
            })
            ->when($cliente, function ($q) {
                return $q->orderBy('f_comprobante_sunats.cliente_id');
            })
            ->when($cliente, function ($q) {
                return $q->orderBy('f_comprobante_sunats.clientRazonSocial');
            })
            ->when($num_documento, function ($q) {
                return $q->orderBy('num_documento');
            })
            ->when($producto, function ($q) {
                return $q->orderBy('f_comprobante_sunat_detalles.codProducto');
            })
            ->when($producto, function ($q) {
                return $q->orderBy('productos.name');
            })
            ->when($fecha_emision, function ($q) {
                return $q->orderBy('fecha_emision');
            })
            ->get();
        //dd($reporte);
        if ($producto) {
            $reporte = $reporte->map(function ($item) use ($productos) {
                $producto = $productos->find($item->codProducto);
                $item->detalle_cantidad = number_format_punto2(intval($item->detalle_cantidad / $producto->cantidad) + ($item->detalle_cantidad % $producto->cantidad) / 100);
                return $item;
            });
        }
        return $reporte;
    }


    public function headings(): array
    {
        $fecha_inicio = date("d-m-Y", strtotime($this->fecha_inicio));
        $fecha_fin = date("d-m-Y", strtotime($this->fecha_fin));

        $ruta = $this->ruta;
        $marcas_name = $this->marcas_name;
        $tipo_documento = $this->tipo_documento;
        $conductor = $this->conductor;
        $vendedor = $this->vendedor;
        $cliente = $this->cliente;
        $num_documento = $this->num_documento;
        $producto = $this->producto;
        $fecha_emision = $this->fecha_emision;

        $titulos = collect([
            ["titulo" => "Ruta Cod", "estado" => $ruta],
            ["titulo" => "Descrip Ruta", "estado" => $ruta],
            ["titulo" => "Descrip Marca", "estado" => $marcas_name],
            ["titulo" => "Tipo Doc", "estado" => $tipo_documento],
            ["titulo" => "Conductor", "estado" => $conductor],
            ["titulo" => "Cod Prevendedor", "estado" => $vendedor],
            ["titulo" => "Nombre Prevendedor", "estado" => $vendedor],
            ["titulo" => "Cod Cliente", "estado" => $cliente],
            ["titulo" => "Nombre Cliente", "estado" => $cliente],
            ["titulo" => "Num Documento", "estado" => $num_documento],
            ["titulo" => "Cod Articulo", "estado" => $producto],
            ["titulo" => "Nombre de Articulo", "estado" => $producto],
            ["titulo" => "Fecha Emision", "estado" => $fecha_emision],
            ["titulo" => "Cantidad Bultos Venta", "estado" => $producto],
            ["titulo" => "Importe Venta", "estado" => true],
        ]);

        $titulos_array = $titulos->filter(function ($titulo) {
            return $titulo['estado'];
        })->map(function ($titulo) {
            return $titulo['titulo'];
        })->toArray();

        $encabezados = [
            ["Reporte de Ventas"],
            ["Fecha Del : $fecha_inicio AL: $fecha_fin"],
            $titulos_array,
        ];

        return $encabezados;
    }
}
