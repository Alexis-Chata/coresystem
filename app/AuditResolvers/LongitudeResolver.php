<?php

namespace App\AuditResolvers;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

class LongitudeResolver implements Resolver
{
    public static function resolve(Auditable $auditable)
    {
        return session('longitud');
    }
}
