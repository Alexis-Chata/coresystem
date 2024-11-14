<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ruta extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'codigo',
        'name',
        'dia_visita',
        'vendedor_id',
        'empresa_id',
        'lista_precio_id',
    ];

    public function vendedor()
    {
        return $this->belongsTo(Empleado::class, 'vendedor_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function listaPrecio()
    {
        return $this->belongsTo(ListaPrecio::class);
    }

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class);
    }
}
