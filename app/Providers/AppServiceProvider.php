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

        // Subscription/billing admin gate referenced by routes + middleware.
        Gate::define('manage subscriptions', function ($user) {
            return $user->hasRoleOrPermission(Role::SUPER_ADMIN, 'manage subscriptions');
        });

        Gate::define('manage students', function ($user) {
            return $user->hasRoleOrPermission(Role::SUPER_ADMIN, 'manage students')
                || $user->hasRole(Role::PARENT);
        });
    }
}
