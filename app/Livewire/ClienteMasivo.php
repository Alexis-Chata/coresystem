<?php

namespace App\Livewire;

use App\Imports\ClientesImport;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class ClienteMasivo extends Component
{
    public function render()
    {
        return view('livewire.cliente-masivo');
    }

    use WithFileUploads;

    public $archivo;

    public function importar()
    {
        $this->validate([
            'archivo' => 'required|file|mimes:xlsx,csv'
        ]);

        Excel::import(new ClientesImport, $this->archivo->getRealPath());

        // Limpiar archivo cargado
        $this->reset('archivo');

        session()->flash('message', 'Clientes importados correctamente.');
    }
}
