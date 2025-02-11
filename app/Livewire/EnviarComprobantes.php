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

    public $fecha_emision;
    public $search;
    public $estado_envio;

    public function mount()
    {
        $this->fecha_emision = Carbon::now();

        if ($this->fecha_emision->isMonday()) {
            $this->fecha_emision = $this->fecha_emision->subDays(2)->toDateString();
        } else {
            $this->fecha_emision = $this->fecha_emision->subDay()->toDateString();
        }
    }

    public function actualizar_table()
    {
        $this->dispatch("actualiza_tabla", fecha: $this->fecha_emision, search: $this->search, estado_envio: $this->estado_envio);
    }

    public function updatedSearch()
    {
        $this->actualizar_table();
    }
}
