<?php

namespace App\Support\Tenant;

use App\Models\Tenant;
use App\Models\User;
use Closure;
use Spatie\Permission\PermissionRegistrar;

/**
 * Holds the tenant active for the current request lifecycle.
 *
 * Registered as a singleton so every part of the request (models, policies,
 * actions) resolves the exact same active tenant. If nothing has been set
 * explicitly it resolves lazily from the authenticated user, which makes the
 * global scope correct even when it runs before route middleware (e.g. during
 * route model binding).
 */
class TenantManager
{
    private ?Tenant $tenant = null;

    private bool $resolved = false;

    private int $bypassDepth = 0;

    public function set(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
        $this->resolved = true;

        if ($tenant !== null) {
            app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getKey());

            if (app()->bound('auth') && ($user = auth()->user()) instanceof User) {
                $user->unsetRelation('roles')->unsetRelation('permissions');
            }
        }
    }

    public function current(): ?Tenant
    {
        if ($this->tenant === null && ! $this->resolved) {
            $this->resolved = true;
            $this->tenant = $this->resolveFromAuth();
        }

        return $this->tenant;
    }

    public function id(): ?int
    {
        return $this->current()?->getKey();
    }

    public function has(): bool
    {
        return $this->current() !== null;
    }

    public function forget(): void
    {
        $this->tenant = null;
        $this->resolved = false;
    }

    public function isBypassed(): bool
    {
        return $this->bypassDepth > 0;
    }

    /**
     * Run a callback with the tenant global scope disabled. Reserved for
     * platform-level work (super admin, onboarding, seeders, reporting across
     * tenants) where a tenant context is intentionally absent.
     */
    public function bypass(Closure $callback): mixed
    {
        $this->bypassDepth++;

        try {
            return $callback();
        } finally {
            $this->bypassDepth--;
        }
    }

    private function resolveFromAuth(): ?Tenant
    {
        if (! app()->bound('auth')) {
            return null;
        }

        $user = auth()->user();

        if (! $user instanceof User || $user->is_super_admin) {
            return null;
        }

        $preferred = request()->hasSession() ? request()->session()->get('active_tenant_id') : null;

        foreach (array_filter([$preferred, $user->last_active_tenant_id]) as $tenantId) {
            if (! $user->belongsToTenant($tenantId)) {
                continue;
            }

            $tenant = Tenant::query()->find($tenantId);

            if ($tenant !== null) {
                app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getKey());
                $user->unsetRelation('roles')->unsetRelation('permissions');

                return $tenant;
            }
        }

        $tenant = $user->defaultTenant();

        if ($tenant !== null) {
            app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getKey());
            $user->unsetRelation('roles')->unsetRelation('permissions');
        }

        return $tenant;
    }
}
