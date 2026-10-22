<?php

namespace App\Providers;

use App\Http\Responses\Auth\LoginResponse as AppLoginResponse;
use App\Models\User;
use App\Support\Access\RoleDefinitions;
use App\Support\Tenant\OutletManager;
use App\Support\Tenant\TenantManager;
use Filament\Auth\Http\Responses\LoginResponse as FilamentLoginResponse;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\PermissionRegistrar;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantManager::class);
        $this->app->singleton(OutletManager::class);

        // Filament resolves this concrete class on login; swapping it makes
        // the post-login landing page role-aware (cashier -> POS, etc.).
        $this->app->bind(FilamentLoginResponse::class, AppLoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability): ?bool {
            if ($user->is_super_admin) {
                return true;
            }

            $tenant = app(TenantManager::class)->current();
            if ($tenant === null) {
                return null;
            }

            app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getKey());

            if ($user->roleIn($tenant) === RoleDefinitions::OWNER) {
                return true;
            }

            return null;
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->header('X-Api-Key') ?: $request->ip());
        });
    }
}
