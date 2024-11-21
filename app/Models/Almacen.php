<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'direccion',
        'telefono',
        'email',
        'encargado_id',
        'empresa_id',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function encargado()
    {
        return $this->belongsTo(User::class, 'encargado_id');
    }
}
