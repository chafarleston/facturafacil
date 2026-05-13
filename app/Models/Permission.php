<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'module', 'status'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }

    public static function getModules(): array
    {
        return [
            'dashboard' => 'Dashboard',
            'companies' => 'Empresas',
            'users' => 'Usuarios',
            'customers' => 'Clientes',
            'products' => 'Productos',
            'categories' => 'Categorías',
            'invoices' => 'Comprobantes',
            'purchases' => 'Compras',
            'suppliers' => 'Proveedores',
            'cashregisters' => 'Caja',
            'pos' => 'Punto de Venta',
            'restaurant' => 'Restaurante',
            'kitchen' => 'Cocina (KDS)',
        ];
    }
}