<?php

namespace App\Livewire;

use App\Exports\ReportesExport;
use App\Models\Empleado;
use App\Models\Marca;
use App\Models\Ruta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class Reportes extends Component
{
    public function render()
    {
        return view('livewire.reportes');
    }

    public $fecha_inicio;
    public $fecha_fin;
    public $rutas;
    public $ruta_id;
    public $marcas;
    public $marca_id;
    public $vendedores;
    public $vendedor_id;
    public $productos;
    public $producto_id;

    public $rutas_name;
    public $marcas_name;
    public $tipo_documento;
    public $conductor;
    public $vendedor;
    public $cliente;
    public $num_documento;
    public $producto;
    public $fecha_emision;

    public function mount()
    {
        $this->fecha_inicio = Carbon::now()->startOfMonth()->toDateString();
        $this->fecha_fin = Carbon::now();

        if ($this->fecha_fin->isMonday()) {
            $this->fecha_fin = $this->fecha_fin->subDays(2)->toDateString(); // Agregar 2 días si es sábado
        } else {
            $this->fecha_fin = $this->fecha_fin->subDay()->toDateString(); // Agregar 1 día en otros casos
        }

        $rutas_id = DB::table('f_comprobante_sunat_detalles')
            ->join('f_comprobante_sunats', 'f_comprobante_sunat_detalles.f_comprobante_sunat_id', '=', 'f_comprobante_sunats.id')
            ->join('productos', 'f_comprobante_sunat_detalles.codProducto', '=', 'productos.id')
            ->select('f_comprobante_sunats.ruta_id')
            ->whereBetween('pedido_fecha_factuacion', [$this->fecha_inicio, $this->fecha_fin])
            ->where("estado_reporte", true)
            ->distinct()
            ->pluck('ruta_id');
        $this->rutas = Ruta::whereIn('id', $rutas_id)->get();

        $marcas_id = DB::table('f_comprobante_sunat_detalles')
            ->join('f_comprobante_sunats', 'f_comprobante_sunat_detalles.f_comprobante_sunat_id', '=', 'f_comprobante_sunats.id')
            ->join('productos', 'f_comprobante_sunat_detalles.codProducto', '=', 'productos.id')
            ->select('productos.marca_id')
            ->whereBetween('pedido_fecha_factuacion', [$this->fecha_inicio, $this->fecha_fin])
            ->where("estado_reporte", true)
            ->distinct()
            ->pluck('marca_id');
        $this->marcas = Marca::whereIn('id', $marcas_id)->get();

        $vendedores_id = DB::table('f_comprobante_sunat_detalles')
            ->join('f_comprobante_sunats', 'f_comprobante_sunat_detalles.f_comprobante_sunat_id', '=', 'f_comprobante_sunats.id')
            ->join('productos', 'f_comprobante_sunat_detalles.codProducto', '=', 'productos.id')
            ->select('f_comprobante_sunats.vendedor_id')
            ->whereBetween('pedido_fecha_factuacion', [$this->fecha_inicio, $this->fecha_fin])
            ->where("estado_reporte", true)
            ->distinct()
            ->pluck('vendedor_id');
        $this->vendedores = Empleado::whereIn('id', $vendedores_id)->get();

        $this->productos = DB::table('f_comprobante_sunat_detalles')
            ->join('f_comprobante_sunats', 'f_comprobante_sunat_detalles.f_comprobante_sunat_id', '=', 'f_comprobante_sunats.id')
            ->join('productos', 'f_comprobante_sunat_detalles.codProducto', '=', 'productos.id')
            ->select('productos.id', 'productos.name')
            ->whereBetween('pedido_fecha_factuacion', [$this->fecha_inicio, $this->fecha_fin])
            ->where("estado_reporte", true)
            ->distinct()
            ->orderBy('productos.id')
            ->get();
    }

    public function exportar_reporte()
    {
        $fecha_inicio = $this->fecha_inicio;
        $fecha_fin = $this->fecha_fin;
        $ruta_id   = $this->ruta_id;
        $marca_id = $this->marca_id;
        $vendedor_id = $this->vendedor_id;
        $producto_id = $this->producto_id;

        $ruta = $this->rutas_name ?? false;
        $marcas_name = $this->marcas_name ?? false;
        $tipo_documento = $this->tipo_documento ?? false;
        $conductor = $this->conductor ?? false;
        $vendedor = $this->vendedor ?? false;
        $cliente = $this->cliente ?? false;
        $num_documento = $this->num_documento ?? false;
        $producto = $this->producto ?? false;
        $fecha_emision = $this->fecha_emision ?? false;
        //dd($marcas_name, $fecha_emision, $ruta);
        return Excel::download(new ReportesExport($fecha_inicio, $fecha_fin, $ruta_id, $marca_id, $vendedor_id, $producto_id, $ruta, $marcas_name, $tipo_documento, $conductor, $vendedor, $cliente, $num_documento, $producto, $fecha_emision), 'reporte_ventas.xlsx');
    }
}
