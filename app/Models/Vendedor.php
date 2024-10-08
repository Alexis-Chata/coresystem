<?php

namespace App\Models;

class Vendedor extends Empleado
{
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('tipo', function ($query) {
            $query->where('tipo_empleado', 'vendedor');
        });
    }

    protected static function booted()
    {
        static::creating(function ($vendedor) {
            $vendedor->tipo_empleado = 'vendedor';
        });
    }
}
