<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'producto_id',
        'component_id',
        'cantidad',
        'subcantidad',
        'cantidad_total'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function component()
    {
        return $this->belongsTo(Producto::class, 'component_id');
    }
}
