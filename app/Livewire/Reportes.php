<?php

namespace App\Livewire;

use App\Exports\ReportesExport;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class Reportes extends Component
{
    public function render()
    {
        return view('livewire.reportes');
    }

    public function exportar_reporte(){
        $fecha_inicio = "2025-02-01";
        $fecha_fin = "2025-02-07";
        return Excel::download(new ReportesExport($fecha_inicio, $fecha_fin), 'reporte_ventas.xlsx');
    }
}
