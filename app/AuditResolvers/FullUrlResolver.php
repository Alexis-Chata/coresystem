<?php

namespace App\AuditResolvers;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

class FullUrlResolver implements Resolver
{
    public static function resolve(Auditable $auditable)
    {
        // Intentamos primero obtener la URL real desde sesión (pasada desde Livewire)
        $url = session('pagina_actual');

        // Si no existe, usamos la que Laravel ve por defecto (probablemente /livewire/update)
        return $url ?: request()->fullUrl();
    }
}
