<?php

namespace App\AuditResolvers;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

class RouteNameResolver implements Resolver
{
    public static function resolve(Auditable $auditable)
    {
        return session('route_name');
    }
}
