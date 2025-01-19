<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FGuiaDetalleSunat extends Model
{
    /** @use HasFactory<\Database\Factories\FGuiaDetalleSunatFactory> */
    use HasFactory;

    protected $fillable = [
        'cantidad',
        'unidad',
        'serie',
        'descripcion',
        'codigo',
        'f_guia_sunat_id',
    ];
}
