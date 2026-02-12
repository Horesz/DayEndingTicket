<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Nincs bejelentkezve.');
        }

        // Rendszergazda mindent láthat
        if ($user->role && $user->role->name === 'rendszergazda') {
            return $next($request);
        }

        // Szerepkör ellenőrzés
        if ($user->role && in_array($user->role->name, $roles)) {
            return $next($request);
        }

        abort(403, 'Nincs jogosultságod ehhez a művelethez.');
    }
}