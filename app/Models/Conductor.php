<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conductor extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'direccion',
        'celular',
        //'f_tipo_documento_id',
        'numero_documento',
        'numero_brevete',
    ];
}
