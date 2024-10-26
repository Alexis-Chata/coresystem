<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Padron extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'ruta_id',
        'nro_secuencia',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function ruta()
    {
        return $this->belongsTo(Ruta::class);
    }
}
