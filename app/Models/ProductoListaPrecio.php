<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductoListaPrecio extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'producto_lista_precios';

    protected $fillable = [
        'producto_id',
        'lista_precio_id',
        'precio'
    ];

    // Relación con Producto
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    // Relación con ListaPrecio
    public function listaPrecio()
    {
        return $this->belongsTo(ListaPrecio::class);
    }
}
