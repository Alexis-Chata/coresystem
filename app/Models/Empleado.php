<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'name',
        'direccion',
        'celular',
        'f_tipo_documento_id',
        'numero_documento',
        'tipo_empleado',
        'numero_brevete',
    ];

    public function tipoDocumento()
    {
        return $this->belongsTo(F_tipo_documento::class, 'f_tipo_documento_id');
    }
}
