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
        'nro_doc_liquidacion',
        'fecha_liquidacion',
        'comentario',
        'tipo_movimiento_name',
        'empleado_id',
        'estado',
    ];

    public function movimientoDetalles()
    {
        return $this->hasMany(MovimientoDetalle::class);
    }

    public function tipoMovimiento()
    {
        return $this->belongsTo(TipoMovimiento::class);
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }

    public function conductor()
    {
        return $this->belongsTo(Empleado::class, 'conductor_id');
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function pedidos(){
        return $this->hasMany(Pedido::class);
    }
}
