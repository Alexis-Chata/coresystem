<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoDetalle extends Model
{
    /** @use HasFactory<\Database\Factories\MovimientoDetalleFactory> */
    use HasFactory;

    protected $fillable = [
        'movimiento_id',
        'producto_id',
        'cantidad',
        'precio_venta_unitario',
        'precio_venta_total',
        'costo_unitario',
        'costo_total',
        'empleado_id',
    ];

    public function movimiento()
    {
        return $this->belongsTo(Movimiento::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }

    protected $appends = ['cantidad_bultos', 'cantidad_unidades'];

    public function getCantidadBultosAttribute()
    {
        return $this->separarCantidadParte(0);
    }

    public function getCantidadUnidadesAttribute()
    {
        return $this->separarCantidadParte(1);
    }

    /**
     * Separar cantidad en partes (bultos y unidades).
     *
     * @param int $parte 0 para bultos, 1 para unidades.
     * @return int
     */
    private function separarCantidadParte($parte)
    {
        // Usa un separador de punto decimal según configuración de locale
        $separador = localeconv()['decimal_point'] ?? '.';

        // Formatea la cantidad en caso de que no sea válida
        $cantidad = $this->cantidad ?? 0;

        // Separa en partes usando el separador
        $partes = explode($separador, number_format($cantidad, 2, $separador, ''));

        // Devuelve la parte solicitada o 0 si no existe
        return isset($partes[$parte]) ? (int)$partes[$parte] : 0;
    }
}
