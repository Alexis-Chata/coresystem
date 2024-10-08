<?php

namespace App\Models;

class Conductor extends Empleado
{
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('tipo', function ($query) {
            $query->where('tipo_empleado', 'conductor');
        });
    }

    protected static function booted()
    {
        static::creating(function ($conductor) {
            $conductor->tipo_empleado = 'conductor';
        });
    }
}
