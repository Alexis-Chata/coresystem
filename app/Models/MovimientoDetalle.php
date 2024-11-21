<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoDetalle extends Model
{
    /** @use HasFactory<\Database\Factories\MovimientoDetalleFactory> */
    use HasFactory;

    protected $fillable = [
        'movimiento_id',
        'producto_id',
        'cantidad',
        'precio_venta_unitario',
        'precio_venta_total',
        'costo_unitario',
        'costo_total',
        'empleado_id',
    ];
}
