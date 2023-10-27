<?php

namespace App\Http\Middleware;

use Closure;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        if ($request->user() && $request->user()->role != $role) {
            return response('No tienes permiso para acceder a esta ruta.', 403);
        }

        return $next($request);
    }
}
