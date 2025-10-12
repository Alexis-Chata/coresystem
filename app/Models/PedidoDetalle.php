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
        'importe',
        'almacen_producto_id',
        'cantidad_unidades',
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
        $digitos = calcular_digitos($this->producto_cantidad_caja);
        return explode(localeconv()['decimal_point'],number_format($this->cantidad, $digitos, ".", ""))[0] ?? 0;
    }

    public function getQcanpedunidadsAttribute()
    {
        $digitos = calcular_digitos($this->producto_cantidad_caja);
        return explode(localeconv()['decimal_point'], number_format($this->cantidad, $digitos, ".", ""))[1] ?? 0;
    }
}
