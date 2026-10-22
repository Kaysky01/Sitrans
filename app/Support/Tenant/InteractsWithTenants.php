<?php

namespace App\Support\Tenant;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Membership side of tenancy: which tenants a user can access and with which
 * role. Applied to the global User model.
 */
trait InteractsWithTenants
{
    /**
     * @return BelongsToMany<Tenant, $this>
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class)
            ->withPivot(['role', 'is_active', 'joined_at'])
            ->withTimestamps();
    }

    public function belongsToTenant(Tenant|int $tenant): bool
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->getKey() : $tenant;

        return $this->tenants()
            ->whereKey($tenantId)
            ->wherePivot('is_active', true)
            ->exists();
    }

    public function roleIn(Tenant|int $tenant): ?string
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->getKey() : $tenant;

        return $this->tenants()
            ->whereKey($tenantId)
            ->first()?->pivot->role;
    }

    /**
     * The default tenant for this user: the last one they used, otherwise their
     * first active membership.
     */
    public function defaultTenant(): ?Tenant
    {
        if ($this->last_active_tenant_id !== null && $this->belongsToTenant($this->last_active_tenant_id)) {
            return Tenant::find($this->last_active_tenant_id);
        }

        return $this->tenants()
            ->wherePivot('is_active', true)
            ->orderBy('tenant_user.id')
            ->first();
    }
}
