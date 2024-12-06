<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'ruta_id',
        'f_tipo_comprobante_id',
        'vendedor_id',
        'conductor_id',
        'cliente_id',
        'fecha_emision',
        'importe_total',
        'nro_doc_liquidacion',
        'lista_precio',
        'comentario',
        'empresa_id',
    ];
    
    public function pedidoDetalles()
    {
        return $this->hasMany(PedidoDetalle::class);
    }

    public function ruta(): BelongsTo
    {
        return $this->belongsTo(Ruta::class);
    }

    public function tipoComprobante(): BelongsTo
    {
        return $this->belongsTo(FTipoComprobante::class, 'f_tipo_comprobante_id');
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'vendedor_id')->where('tipo_empleado', 'vendedor');
    }

    public function conductor(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'conductor_id')->where('tipo_empleado', 'conductor');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}
