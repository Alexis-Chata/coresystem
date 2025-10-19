<?php

if (!function_exists('format_date')) {
    function format_date($date)
    {
        return \Carbon\Carbon::parse($date)->format('d-m-Y');
    }
}

if (!function_exists('format_date_long')) {
    function format_date_long($date)
    {
        return \Carbon\Carbon::parse($date)->translatedFormat('d-m-Y (l)');
    }
}

if (!function_exists('auth_id')) {
    function auth_id()
    {
        return \Illuminate\Support\Facades\Auth::id();
    }
}

if (!function_exists('auth_user')) {
    /**
     * Retorna el usuario autenticado.
     *
     * @return \App\Models\User|null
     */
    function auth_user()
    {
        return \Illuminate\Support\Facades\Auth::user();
    }
}

if (!function_exists('number_format_punto2')) {
    function number_format_punto2($number)
    {
        return number_format($number, 2, '.', '');
    }
}

if (!function_exists('carbon_parse')) {
    function carbon_parse($date)
    {
        return \Carbon\Carbon::parse($date);
    }
}

if (!function_exists('convertir_a_paquetes')) {
    function convertir_a_paquetes($cajas, $cantidad_en_caja)
    {
        $cantidad_digitos = calcular_digitos($cantidad_en_caja);

        list($nro_cajas, $nro_paquetes) = explode('.', number_format($cajas, $cantidad_digitos, '.', ''));
        $paquetes = $nro_cajas * $cantidad_en_caja + $nro_paquetes;

        return number_format($paquetes, $cantidad_digitos, '.', '');
    }
}

if (!function_exists('convertir_a_cajas')) {
    function convertir_a_cajas($paquetes, $cantidad_en_caja)
    {
        $cantidad_digitos = calcular_digitos($cantidad_en_caja);

        $nro_cajas = intdiv($paquetes, $cantidad_en_caja); // División entera;
        $nro_paquetes = $paquetes % $cantidad_en_caja; // Residuo;

        $cajas = $nro_cajas + ($nro_paquetes / (10 ** $cantidad_digitos));
        return number_format($cajas, $cantidad_digitos, '.', '');
    }
}

if (!function_exists('calcular_digitos')) {
    function calcular_digitos($factor): int
    {
        // Asegurar que sea número y al menos 1
        $f = max(1, (int) $factor);

        // Ejemplo: factor=1000 -> maxUnits=999
        $maxUnits = max(0, $f - 1);

        // Contar longitud de los dígitos (mínimo 2)
        $digits = max(2, strlen((string) abs((int) floor($maxUnits))));

        return $digits;
    }
}
