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

        // Lectura de productos/categorías por permiso (solo lectura)
        $productReadRoutes = [
            'products.index', 'products.show',
            'products.composite.create',
            'products.inventory.report', 'products.inventory.report.excel', 'products.inventory.report.pdf',
        ];
        $categoryReadRoutes = ['categories.index'];

        if (in_array($routeName, $productReadRoutes) && $user->hasPermission('view_products')) {
            return $next($request);
        }
        if (in_array($routeName, $categoryReadRoutes) && $user->hasPermission('view_categories')) {
            return $next($request);
        }

        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return $next($request);
        }

        abort(403);
    }
}