<?php

if (!function_exists('format_date')) {
    function format_date($date)
    {
        return \Carbon\Carbon::parse($date)->format('d/m/Y');
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
