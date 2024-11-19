<?php

namespace App\Livewire\Forms;

use App\Models\FSerie;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class FserieForm extends Form
{
    public ?FSerie $fserie;

    public $serie;
    public $correlativo;
    public $fechaemision;
    public $f_sede_id;
    public $f_tipo_comprobante_id;

    public function set(FSerie $fserie)
    {
        $this->fserie = $fserie;
        $this->serie = $fserie->serie;
        $this->correlativo = $fserie->correlativo;
        $this->fechaemision = $fserie->fechaemision;
        $this->f_sede_id = $fserie->f_sede_id;
        $this->f_tipo_comprobante_id = $fserie->f_tipo_comprobante_id;
    }

    public function update()
    {
        $this->validate();
        $this->fserie->update($this->pull(['serie', 'correlativo', 'fechaemision', 'f_sede_id', 'f_tipo_comprobante_id']));
    }

    public function store()
    {
        $this->validate();
        //dd($this->all());
        if (isset($this->fserie)) {
            $this->update();
        } else {
            $this->fserie = FSerie::create($this->pull(['serie', 'correlativo', 'fechaemision', 'f_sede_id', 'f_tipo_comprobante_id']));
        }
        $this->reset();
    }

    public function rules()
    {
        $rules_serie = [
            'required',
            'string',
            'min:4',
            'max:4',
            'unique'
        ];
        if (isset($this->fserie)) {
            $rules_serie[] = Rule::unique('f_series')->ignore($this->fserie);
        } else {
            $rules_serie[] = 'unique:f_series';
        }
        return [
            'serie' => $rules_serie,
            'correlativo' => 'required',
            'fechaemision' => 'required',
            'f_sede_id' => 'required|exists:f_sedes,id',
            'f_tipo_comprobante_id' => 'required|exists:f_tipo_comprobantes,id',
        ];
    }
}
