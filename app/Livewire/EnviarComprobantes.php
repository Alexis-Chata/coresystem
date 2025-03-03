<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;

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
}
