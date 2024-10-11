<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'marca_id',
        'categoria_id',
        'f_tipo_afectacion_id',
        'porcentaje_igv',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function tipoAfectacion()
    {
        return $this->belongsTo(F_tipo_afectacion::class, 'f_tipo_afectacion_id');
    }
}
