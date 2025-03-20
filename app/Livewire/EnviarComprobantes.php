<?php

namespace App\Livewire;

use App\Exports\FComprobanteSunatsExport;
use Carbon\Carbon;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class EnviarComprobantes extends Component
{
    public function render()
    {
        return view('livewire.enviar-comprobantes');
    }

    public $fecha_emision_inicio;
    public $fecha_emision_fin;
    public $search;
    public $estado_envio;
    public $tipo_comprobante;

    public function mount()
    {
        $this->fecha_emision_inicio = Carbon::now();

        if ($this->fecha_emision_inicio->isMonday()) {
            $this->fecha_emision_inicio = $this->fecha_emision_inicio->subDays(2)->toDateString();
        } else {
            $this->fecha_emision_inicio = $this->fecha_emision_inicio->subDay()->toDateString();
        }
        $this->fecha_emision_fin = $this->fecha_emision_inicio;
    }

    public function actualizar_table()
    {
        $this->dispatch("actualiza_tabla", fecha_inicio: $this->fecha_emision_inicio, fecha_fin: $this->fecha_emision_fin, search: $this->search, estado_envio: $this->estado_envio, tipoDoc: $this->tipo_comprobante);
    }

    public function updatedSearch()
    {
        $this->actualizar_table();
    }

    public function descargar_comprobantes()
    {
        $inicio = $this->fecha_emision_inicio;
        $fin = $this->fecha_emision_fin;
        return Excel::download(new FComprobanteSunatsExport($inicio, $fin), 'Reporte_Comprobantes_' . format_date($inicio) . '_' . format_date($fin) . '.xlsx');
    }
}
