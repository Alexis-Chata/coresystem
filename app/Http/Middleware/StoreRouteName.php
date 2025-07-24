<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StoreRouteName
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->route()) {
            // Guarda el nombre de la ruta en la sesión
            session([
                'pagina_actual' => $request->fullUrl(),
                'route_name' => $request->route()->getName(),
            ]);
        }
        return $next($request);
    }
}
