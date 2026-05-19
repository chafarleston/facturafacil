<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'Ver Dashboard', 'slug' => 'view_dashboard', 'module' => 'dashboard', 'description' => 'Ver el dashboard'],
            ['name' => 'Ver Empresas', 'slug' => 'view_companies', 'module' => 'companies', 'description' => 'Ver lista de empresas'],
            ['name' => 'Crear Empresas', 'slug' => 'create_companies', 'module' => 'companies', 'description' => 'Crear empresas'],
            ['name' => 'Editar Empresas', 'slug' => 'edit_companies', 'module' => 'companies', 'description' => 'Editar empresas'],
            ['name' => 'Eliminar Empresas', 'slug' => 'delete_companies', 'module' => 'companies', 'description' => 'Eliminar empresas'],
            ['name' => 'Ver Usuarios', 'slug' => 'view_users', 'module' => 'users', 'description' => 'Ver lista de usuarios'],
            ['name' => 'Crear Usuarios', 'slug' => 'create_users', 'module' => 'users', 'description' => 'Crear usuarios'],
            ['name' => 'Editar Usuarios', 'slug' => 'edit_users', 'module' => 'users', 'description' => 'Editar usuarios'],
            ['name' => 'Eliminar Usuarios', 'slug' => 'delete_users', 'module' => 'users', 'description' => 'Eliminar usuarios'],
            ['name' => 'Ver Clientes', 'slug' => 'view_customers', 'module' => 'customers', 'description' => 'Ver lista de clientes'],
            ['name' => 'Crear Clientes', 'slug' => 'create_customers', 'module' => 'customers', 'description' => 'Crear clientes'],
            ['name' => 'Editar Clientes', 'slug' => 'edit_customers', 'module' => 'customers', 'description' => 'Editar clientes'],
            ['name' => 'Eliminar Clientes', 'slug' => 'delete_customers', 'module' => 'customers', 'description' => 'Eliminar clientes'],
            ['name' => 'Ver Productos', 'slug' => 'view_products', 'module' => 'products', 'description' => 'Ver lista de productos'],
            ['name' => 'Crear Productos', 'slug' => 'create_products', 'module' => 'products', 'description' => 'Crear productos'],
            ['name' => 'Editar Productos', 'slug' => 'edit_products', 'module' => 'products', 'description' => 'Editar productos'],
            ['name' => 'Eliminar Productos', 'slug' => 'delete_products', 'module' => 'products', 'description' => 'Eliminar productos'],
            ['name' => 'Ver Categorías', 'slug' => 'view_categories', 'module' => 'categories', 'description' => 'Ver lista de categorías'],
            ['name' => 'Crear Categorías', 'slug' => 'create_categories', 'module' => 'categories', 'description' => 'Crear categorías'],
            ['name' => 'Editar Categorías', 'slug' => 'edit_categories', 'module' => 'categories', 'description' => 'Editar categorías'],
            ['name' => 'Eliminar Categorías', 'slug' => 'delete_categories', 'module' => 'categories', 'description' => 'Eliminar categorías'],
            ['name' => 'Ver Comprobantes', 'slug' => 'view_invoices', 'module' => 'invoices', 'description' => 'Ver lista de comprobantes'],
            ['name' => 'Crear Comprobantes', 'slug' => 'create_invoices', 'module' => 'invoices', 'description' => 'Crear comprobantes'],
            ['name' => 'Enviar SUNAT', 'slug' => 'send_sunat', 'module' => 'invoices', 'description' => 'Enviar comprobantes a SUNAT'],
            ['name' => 'Ver Compras', 'slug' => 'view_purchases', 'module' => 'purchases', 'description' => 'Ver lista de compras'],
            ['name' => 'Crear Compras', 'slug' => 'create_purchases', 'module' => 'purchases', 'description' => 'Crear compras'],
            ['name' => 'Ver Proveedores', 'slug' => 'view_suppliers', 'module' => 'suppliers', 'description' => 'Ver lista de proveedores'],
            ['name' => 'Crear Proveedores', 'slug' => 'create_suppliers', 'module' => 'suppliers', 'description' => 'Crear proveedores'],
            ['name' => 'Ver Caja', 'slug' => 'view_cashregisters', 'module' => 'cashregisters', 'description' => 'Ver caja'],
            ['name' => 'Abrir Caja', 'slug' => 'open_cashregister', 'module' => 'cashregisters', 'description' => 'Abrir caja'],
            ['name' => 'Cerrar Caja', 'slug' => 'close_cashregister', 'module' => 'cashregisters', 'description' => 'Cerrar caja'],
            ['name' => 'Ver POS', 'slug' => 'view_pos', 'module' => 'pos', 'description' => 'Ver punto de venta'],
            ['name' => 'Usar POS', 'slug' => 'use_pos', 'module' => 'pos', 'description' => 'Usar punto de venta'],
            ['name' => 'Ver Restaurante', 'slug' => 'view_restaurant', 'module' => 'restaurant', 'description' => 'Ver módulo restaurante'],
            ['name' => 'Gestionar Pedidos', 'slug' => 'manage_orders', 'module' => 'restaurant', 'description' => 'Gestionar pedidos de restaurante'],
            ['name' => 'Ver Cocina', 'slug' => 'view_kitchen', 'module' => 'kitchen', 'description' => 'Ver cocina KDS'],
            ['name' => 'Gestionar Cocina', 'slug' => 'manage_kitchen', 'module' => 'kitchen', 'description' => 'Gestionar pedidos en cocina'],
            ['name' => 'Ver Roles', 'slug' => 'view_roles', 'module' => 'users', 'description' => 'Ver roles'],
            ['name' => 'Crear Roles', 'slug' => 'create_roles', 'module' => 'users', 'description' => 'Crear roles'],
            ['name' => 'Editar Roles', 'slug' => 'edit_roles', 'module' => 'users', 'description' => 'Editar roles'],
            ['name' => 'Ver Permisos', 'slug' => 'view_permissions', 'module' => 'users', 'description' => 'Ver permisos'],
            ['name' => 'Crear Permisos', 'slug' => 'create_permissions', 'module' => 'users', 'description' => 'Crear permisos'],
            ['name' => 'Ver Series', 'slug' => 'view_series', 'module' => 'series', 'description' => 'Ver series'],
            ['name' => 'Ver Impresoras', 'slug' => 'view_printers', 'module' => 'printers', 'description' => 'Ver configuración de impresoras'],
            ['name' => 'Ver Cola Impresión', 'slug' => 'view_print_queue', 'module' => 'printers', 'description' => 'Ver cola de impresión'],
            ['name' => 'Ver Modo Pedidos', 'slug' => 'view_order_mode', 'module' => 'restaurant', 'description' => 'Ver configuración del modo de pedidos'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['slug' => $perm['slug']], $perm);
        }

        $adminRole = Role::firstOrCreate(['slug' => 'admin'], [
            'name' => 'Administrador',
            'description' => 'Rol de administrador con todos los permisos',
            'is_system' => true,
            'status' => true,
        ]);
        $adminRole->syncPermissions(Permission::whereIn('slug', [
            'view_dashboard', 'view_companies', 'create_companies', 'edit_companies',
            'view_users', 'create_users', 'edit_users', 'delete_users',
            'view_customers', 'create_customers', 'edit_customers', 'delete_customers',
            'view_products', 'create_products', 'edit_products', 'delete_products',
            'view_categories', 'create_categories', 'edit_categories', 'delete_categories',
            'view_invoices', 'create_invoices', 'send_sunat',
            'view_purchases', 'create_purchases',
            'view_suppliers', 'create_suppliers',
            'view_cashregisters', 'open_cashregister', 'close_cashregister',
            'view_pos', 'use_pos',
            'view_restaurant', 'manage_orders',
            'view_kitchen', 'manage_kitchen',
            'view_roles', 'create_roles', 'edit_roles',
            'view_permissions', 'create_permissions',
            'view_series',
            'view_printers', 'view_print_queue',
            'view_order_mode',
        ])->pluck('id')->toArray());

        $mozoRole = Role::firstOrCreate(['slug' => 'mozo'], [
            'name' => 'Mozo',
            'description' => 'Personal de restaurante',
            'is_system' => true,
            'status' => true,
        ]);
        $mozoRole->syncPermissions(Permission::whereIn('slug', [
            'view_dashboard',
            'view_restaurant', 'manage_orders',
            'view_kitchen', 'manage_kitchen',
            'view_pos', 'use_pos',
        ])->pluck('id')->toArray());

        $cajeroRole = Role::firstOrCreate(['slug' => 'cajero'], [
            'name' => 'Cajero',
            'description' => 'Personal de caja y punto de venta',
            'is_system' => true,
            'status' => true,
        ]);
        $cajeroRole->syncPermissions(Permission::whereIn('slug', [
            'view_dashboard',
            'view_invoices', 'create_invoices',
            'view_pos', 'use_pos',
            'view_cashregisters', 'open_cashregister', 'close_cashregister',
        ])->pluck('id')->toArray());
    }
}