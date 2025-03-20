<?php

if (!function_exists('format_date')) {
    function format_date($date)
    {
        return \Carbon\Carbon::parse($date)->format('d-m-Y');
    }
}

if (!function_exists('auth_id')) {
    function auth_id()
    {
        return auth()->id();
    }
}

if (!function_exists('auth_user')) {
    function auth_user()
    {
        return auth()->user();
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
        $cantidad_digitos = (strlen((string) $cantidad_en_caja)) == '1' ? 2 : strlen((string) $cantidad_en_caja);
        list($nro_cajas, $nro_paquetes) = explode('.', number_format($cajas, $cantidad_digitos, '.', ''));
        $paquetes = $nro_cajas * $cantidad_en_caja + $nro_paquetes;
        return number_format($paquetes, $cantidad_digitos, '.', '');
    }
}

if (!function_exists('convertir_a_cajas')) {
    function convertir_a_cajas($paquetes, $cantidad_en_caja)
    {
        $cantidad_digitos = (strlen((string) $cantidad_en_caja)) == '1' ? 2 : strlen((string) $cantidad_en_caja);

        $nro_cajas = intdiv($paquetes, $cantidad_en_caja); // División entera;
        $nro_paquetes = $paquetes % $cantidad_en_caja; // Residuo;

        $cajas = $nro_cajas + ($nro_paquetes / (10 ** $cantidad_digitos));
        return number_format($cajas, $cantidad_digitos, '.', '');
    }
}
