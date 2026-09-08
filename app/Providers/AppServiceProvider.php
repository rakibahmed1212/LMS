<?php

namespace App\Providers;

use App\Models\Role;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Gate::before(function ($user, $ability) {
            return $user->hasRole(Role::SUPER_ADMIN) ? true : null;
        });

        Gate::define('manage students', function ($user) {
            return $user->hasRoleOrPermission(Role::SUPER_ADMIN, 'manage students')
                || $user->hasRole(Role::PARENT);
        });

        foreach ([
            'manage content',
            'create lesson',
            'edit lesson',
            'delete lesson',
            'create quiz',
            'create assignment',
            'upload worksheet',
            'view students',
            'manage subscriptions',
            'view subscriptions',
            'manage payments',
            'view payments',
            'view reports',
            'view analytics',
            'manage staff',
            'manage plans',
            'view audit logs',
        ] as $permission) {
            Gate::define($permission, fn ($user) => $user->hasRoleOrPermission(Role::SUPER_ADMIN, $permission));
        }
    }
}
