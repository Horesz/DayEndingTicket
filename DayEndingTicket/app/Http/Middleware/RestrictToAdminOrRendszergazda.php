<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictToAdminOrRendszergazda
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->role->name, ['admin', 'rendszergazda'])) {
            abort(403, 'Nincs jogosultságod új felhasználó felvételéhez.');
        }

        return $next($request);
    }
}