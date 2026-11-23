<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        // Comprobar que el usuario esté autenticado y activo.
        if (!$usuario || !$usuario->is_active) {
            abort(403, 'Tu cuenta no tiene acceso a esta sección.');
        }

        // Obtener el slug del rol del usuario.
        $rolUsuario = $usuario->role?->slug;

        // Comprobar si su rol está autorizado.
        if (!$rolUsuario || !in_array($rolUsuario, $roles, true)) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
