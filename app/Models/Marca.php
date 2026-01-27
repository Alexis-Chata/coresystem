<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'name',
        'empresa_id',
        'nro_orden',
        'resaltar_cobertura',
        'color_identificador',
    ];

    protected $casts = [
        'resaltar_cobertura' => 'boolean',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }
}
