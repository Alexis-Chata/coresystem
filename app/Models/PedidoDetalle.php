<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class PedidoDetalle extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'pedido_id',
        'item',
        'producto_id',
        'producto_name',
        'cantidad',
        'producto_precio',
        'producto_cantidad_caja',
        'lista_precio',
        'importe'
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    protected $appends = ['qcanpedbultos', 'qcanpedunidads'];

    public function getQcanpedbultosAttribute()
    {
        return explode(localeconv()['decimal_point'],number_format_punto2($this->cantidad))[0] ?? 0;
    }

    public function getQcanpedunidadsAttribute()
    {
        return explode(localeconv()['decimal_point'], number_format_punto2($this->cantidad))[1] ?? 0;
    }
}
