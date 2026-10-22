<?php

namespace App\Support\Tenant;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Apply to any model that belongs to a single tenant via a `tenant_id` column.
 *
 * Provides an automatic global scope so queries never cross tenants and fills
 * `tenant_id` on create from the active tenant.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model): void {
            if ($model->getAttribute('tenant_id') !== null) {
                return;
            }

            $manager = app(TenantManager::class);

            if ($manager->id() !== null) {
                $model->setAttribute('tenant_id', $manager->id());

                return;
            }

            if (! $manager->isBypassed()) {
                throw TenantNotResolvedException::forCreate($model::class);
            }
        });
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Query across all tenants, optionally limited to one. The only sanctioned
     * way to escape the global scope.
     *
     * @return Builder<static>
     */
    public static function withoutTenantScope(?int $tenantId = null): Builder
    {
        $query = static::withoutGlobalScope(TenantScope::class);

        if ($tenantId !== null) {
            $query->where((new static)->qualifyColumn('tenant_id'), $tenantId);
        }

        return $query;
    }
}
