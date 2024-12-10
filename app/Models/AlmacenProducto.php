<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlmacenProducto extends Model
{
    use HasFactory;

    protected $fillable = [
        'producto_id',
        'almacen_id',
        'stock_disponible',
        'stock_fisico',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }
}
