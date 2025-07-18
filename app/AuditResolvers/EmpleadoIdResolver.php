<?php

namespace App\AuditResolvers;

use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

class EmpleadoIdResolver implements Resolver
{
    public static function resolve(Auditable $auditable)
    {
        return Auth::check() ? Auth::user()->user_empleado_main->empleado_id : null;
    }
}
