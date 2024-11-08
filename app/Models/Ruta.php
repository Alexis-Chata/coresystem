<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruta extends Model
{
    use HasFactory;

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
        return $this->belongsTo(Lista_precio::class);
    }
}
