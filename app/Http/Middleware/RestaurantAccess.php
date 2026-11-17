<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RestaurantAccess
{
    public function handle(Request $request, Closure $next)
    {
        $routeName = $request->route()?->getName() ?: '';

        // Este middleware solo aplica al módulo restaurante.
        if (!str_starts_with($routeName, 'restaurant')) {
            return $next($request);
        }

        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        $tableCrud = in_array($routeName, [
            'restaurant.tables.create',
            'restaurant.tables.store',
            'restaurant.tables.edit',
            'restaurant.tables.update',
            'restaurant.tables.destroy',
        ], true);

        // La configuración física de pisos y mesas es solo del administrador.
        if (str_starts_with($routeName, 'restaurant.floors') || $tableCrud) {
            if (!in_array($user->role, ['admin', 'superadmin'], true)) {
                abort(403);
            }

            return $next($request);
        }

        $permission = match (true) {
            str_starts_with($routeName, 'restaurant.kitchenBatch') => 'manage_kitchen',
            str_starts_with($routeName, 'restaurant.kitchen') => 'view_kitchen',
            in_array($routeName, ['restaurant.mode', 'restaurant.toggleMode'], true) => 'view_order_mode',
            in_array($routeName, [
                'restaurant.index',
                'restaurant.orders.show',
                'restaurant.poll',
                'restaurant.tables.order',
                'restaurant.printStatus',
                'restaurant-settings.edit',
                'restaurant-settings.update',
            ], true) => 'view_restaurant',
            str_starts_with($routeName, 'restaurant.kiosk') => 'manage_orders',
            str_starts_with($routeName, 'restaurant.floors') => 'manage_orders',
            str_starts_with($routeName, 'restaurant.tables') => 'manage_orders',
            str_starts_with($routeName, 'restaurant.orders') => 'manage_orders',
            default => 'view_restaurant',
        };

        if (!$user->hasPermission($permission)) {
            abort(403);
        }

        return $next($request);
    }
}
