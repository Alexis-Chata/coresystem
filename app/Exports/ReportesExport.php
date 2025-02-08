<?php

namespace App\Exports;

use App\Models\FComprobanteSunat;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportesExport implements FromCollection, WithHeadings
{
    private $fecha_inicio;
    private $fecha_fin;
    private $marca;

    public function __construct($fecha_inicio, $fecha_fin, $marca="all")
    {
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin = $fecha_fin;
        $this->marca = "all";
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {

        return FComprobanteSunat::whereBetween('pedido_fecha_factuacion', [$this->fecha_inicio, $this->fecha_fin])->where("estado_reporte", true)->get();
    }


    public function headings(): array
    {
        $encabezados = [
            "Descrip Marca",
            "Cod Prevendedor",
            "Nombre Prevendedor",
            "Cod Cliente",
            "Nombre Cliente",
            "Cod Articulo",
            "Num Documento",
            "Nombre de Articulo",
            "Fecha Emision",
            "Cantidad Bultos Venta",
            "Importe Venta",
        ];
        if($this->fecha_fin){
            $encabezados[] = "fecha_fin";
        }

        return $encabezados;
    }
}
