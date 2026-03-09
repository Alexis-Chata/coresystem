<?php

namespace App\Livewire;

use App\Models\Producto;
use Livewire\Component;

class Promociones extends Component
{
    public function render()
    {
        return view('livewire.promociones');
    }

    public $lista_promociones = [];

    public function mount()
    {
        $this->lista_promociones = Producto::with(['marca'])->where('name', "like", "%+%")->orderBy('marca_id')->orderBy('id')->get();
    }
}
