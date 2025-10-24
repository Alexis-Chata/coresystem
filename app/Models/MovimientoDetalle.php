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
        'cantidad_total_unidades',
        'factor',
    ];

    public function movimiento()
    {
        return $this->belongsTo(Movimiento::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class)->withTrashed();
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
        $cantidad = $this->cantidad ?? 0;

        // 🔹 Obtener el factor desde el producto relacionado
        $factor = $this->producto?->cantidad ?? 1;

        // 🔹 Calcular cuántos dígitos decimales corresponden
        $digitos = calcular_digitos($factor);

        // 🔹 Convertir la cantidad a string con los decimales correctos
        $partes = explode('.', number_format($cantidad, $digitos, '.', ''));

        // 🔹 Retornar bultos (0) o unidades (1)
        return isset($partes[$parte]) ? (int)$partes[$parte] : 0;
    }
}
