<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $fillable = [
        'ruc',
        'razon_social',
        'name_comercial',
        'direccion',
        'logo_path',
        'cert_path',
        'sol_user',
        'sol_pass',
        'client_id',
        'client_secret',
        'production',
    ];
}
