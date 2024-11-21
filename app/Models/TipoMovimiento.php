<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoMovimiento extends Model
{
    /** @use HasFactory<\Database\Factories\TipoMovimientoFactory> */
    use HasFactory;

    protected $fillable = [
        'codigo',
        'name',
        'descripcion',
        'empleado_id',
    ];
}
