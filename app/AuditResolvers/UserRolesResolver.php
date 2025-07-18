<?php

namespace App\AuditResolvers;

use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

class UserRolesResolver implements Resolver
{
    public static function resolve(Auditable $auditable)
    {
        return Auth::check() ? json_encode(Auth::user()->roles->pluck('name')->toArray()) : json_encode([]);
    }
}
