<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenant\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the active tenant for an authenticated user, binds it into the
 * TenantManager (which also sets the spatie permissions team) and rejects
 * users without membership. Super admins run without a tenant (platform).
 */
class ResolveTenant
{
    public function __construct(private readonly TenantManager $tenants) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || $user->is_super_admin) {
            return $next($request);
        }

        $tenant = $this->resolveFor($user, $request);

        if ($tenant === null) {
            abort(403, 'Akun Anda belum terhubung ke kafe mana pun.');
        }

        $this->tenants->set($tenant);

        try {
            return $next($request);
        } finally {
            $this->tenants->forget();
            app(PermissionRegistrar::class)->setPermissionsTeamId(null);
        }
    }

    private function resolveFor(User $user, Request $request): ?Tenant
    {
        $preferred = $request->hasSession() ? $request->session()->get('active_tenant_id') : null;

        foreach (array_filter([$preferred, $user->last_active_tenant_id]) as $tenantId) {
            if (! $user->belongsToTenant($tenantId)) {
                continue;
            }

            $tenant = Tenant::query()->find($tenantId);

            if ($tenant !== null) {
                return $tenant;
            }
        }

        return $user->defaultTenant();
    }
}
