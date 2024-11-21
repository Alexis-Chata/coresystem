<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    /** @use HasFactory<\Database\Factories\MovimientoFactory> */
    use HasFactory;

    protected $fillable = [
        'almacen_id',
        'tipo_movimiento_id',
        'fecha_movimiento',
        'conductor_id',
        'vehiculo_id',
        'fecha_liquidacion',
        'comentario',
        'tipo_movimiento_name',
        'empleado_id',
    ];
}
