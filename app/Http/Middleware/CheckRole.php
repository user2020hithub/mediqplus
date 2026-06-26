<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Verifica que el usuario autenticado tenga uno de los roles permitidos.
     * Uso en rutas: ->middleware('role:admin') o ->middleware('role:admin,medico')
     */
    public function handle(Request $request, Closure $next, string ...$rolesPermitidos): Response
    {
        $usuario = $request->user();

        // Si no hay usuario autenticado, Sanctum/auth ya debería haber bloqueado antes.
        // Esta verificación es una segunda capa de seguridad (defensa en profundidad).
        if (!$usuario) {
            abort(401, 'No autenticado.');
        }

        if (!in_array($usuario->rol, $rolesPermitidos)) {
            // El usuario está autenticado pero no tiene el rol correcto.
            // Se responde 403 (Prohibido) y no 404, para ser explícitos en auditoría.
            abort(403, 'No tiene permisos para acceder a este recurso.');
        }

        return $next($request);
    }
}
