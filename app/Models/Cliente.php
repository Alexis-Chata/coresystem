<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'razon_social',
        'direccion',
        'clientecol',
        'f_tipo_documento_id',
        'numero_documento',
        'celular',
        'empresa_id',
        'lista_precio_id',
    ];

    public function tipoDocumento()
    {
        return $this->belongsTo(F_tipo_documento::class, 'f_tipo_documento_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function listaPrecio()
    {
        return $this->belongsTo(ListaPrecio::class);
    }
}
