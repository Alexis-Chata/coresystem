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
        'vendedor_id',
        'empresa_id',
    ];

    public function vendedor()
    {
        return $this->belongsTo(Empleado::class, 'vendedor_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
