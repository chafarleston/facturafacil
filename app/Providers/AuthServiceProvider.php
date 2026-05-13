<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
public function boot()
    {
        $this->registerPolicies();
        Gate::define('admin', function ($user) {
            return $user && ($user->isAdmin() || $user->isSuperAdmin());
        });
        Gate::define('restaurant', function ($user) {
            return $user && ($user->isAdmin() || $user->isSuperAdmin() || $user->isMozo());
        });
        Gate::define('permission', function ($user, string $permissionSlug) {
            return $user && $user->hasPermission($permissionSlug);
        });
    }
}
