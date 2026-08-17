<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Super Admins manage the whole SaaS platform and bypass per-organization
        // policy checks entirely. Tenant data isolation itself is enforced at the
        // query level by OrganizationScope, not by this gate.
        Gate::before(fn (User $user) => $user->is_super_admin ? true : null);
    }
}
