<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'is_main_company',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_main_company' => 'boolean',
        'role' => 'string',
    ];
    
    const ROLE_ADMIN = 'admin';
    const ROLE_USER = 'user';
    const ROLE_SUPERADMIN = 'superadmin';
    const ROLE_WAITER = 'mozo';
    
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }
    
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    public function isMozo(): bool
    {
        return $this->role === self::ROLE_WAITER;
    }
    
    public function hasAccessToRestaurant(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPERADMIN, self::ROLE_WAITER]);
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->isAdmin() || $this->isSuperAdmin()) {
            return true;
        }

        if ($this->roles()->whereHas('permissions', function($q) use ($permissionSlug) {
            $q->where('slug', $permissionSlug);
        })->exists()) {
            return true;
        }

        return Role::where('slug', $this->role)
            ->whereHas('permissions', fn($q) => $q->where('slug', $permissionSlug))
            ->exists();
    }
    
    public function getMainCompany()
    {
        $user = self::where('is_main_company', true)->first();
        if (!$user) {
            $user = self::first();
        }
        return $user ? $user->company : null;
}
}
