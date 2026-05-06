<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
    
    // Role helpers
    const ROLE_ADMIN = 'admin';
    const ROLE_USER = 'user';
    const ROLE_SUPERADMIN = 'superadmin';
    
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
    
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    
    public static function getMainCompany()
    {
        $user = self::where('is_main_company', true)->first();
        if (!$user) {
            $user = self::first();
        }
        return $user ? $user->company : null;
    }
}
