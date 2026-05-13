<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            abort(403);
        }

        $user = $request->user();
        $routeName = $request->route()?->getName() ?: '';

        if (str_starts_with($routeName, 'restaurant.')) {
            if (in_array($user->role, ['admin', 'superadmin', 'mozo'])) {
                return $next($request);
            }
            abort(403);
        }

        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return $next($request);
        }

        abort(403);
    }
}