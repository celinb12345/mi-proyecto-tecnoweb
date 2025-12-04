<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Verifica que el usuario autenticado tenga el rol indicado.
     * Uso en rutas: ->middleware('role:cliente'), etc.
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = Auth::user();

        // Si no hay usuario o el rol no coincide → lo sacamos al login
        if (! $user || $user->role !== $role) {
            Auth::logout();
            return redirect()->route('login');
        }

        return $next($request);
    }
}
