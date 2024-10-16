<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class F_tipo_afectacion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'descripcion',
        'letra',
        'codigo',
        'tipo'
    ];
}
