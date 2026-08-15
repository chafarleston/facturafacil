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

        // Escritura de productos/categorías por permiso
        $writeRoutePermission = [
            'products.export' => 'create_products',
            'products.create' => 'create_products',
            'products.store' => 'create_products',
            'products.composite.store' => 'create_products',
            'products.duplicate' => 'create_products',
            'products.import.form' => 'create_products',
            'products.import.store' => 'create_products',
            'products.import.preview' => 'create_products',
            'products.import.template' => 'create_products',
            'products.edit' => 'edit_products',
            'products.update' => 'edit_products',
            'products.composite.edit' => 'edit_products',
            'products.composite.update' => 'edit_products',
            'products.destroy' => 'delete_products',
            'categories.create' => 'create_categories',
            'categories.store' => 'create_categories',
            'categories.edit' => 'edit_categories',
            'categories.update' => 'edit_categories',
            'categories.destroy' => 'delete_categories',
        ];

        if (isset($writeRoutePermission[$routeName]) && $user->hasPermission($writeRoutePermission[$routeName])) {
            return $next($request);
        }

        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return $next($request);
        }

        abort(403);
    }
}