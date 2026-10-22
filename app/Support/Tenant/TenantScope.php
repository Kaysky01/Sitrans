<?php

namespace App\Support\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Restricts every query to the active tenant.
 *
 * - Active tenant set  -> filter by tenant_id.
 * - Explicitly bypassed -> no filter (platform-level work).
 * - Neither            -> throw, so a missing tenant context can never silently
 *                         leak rows across tenants.
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $manager = app(TenantManager::class);

        if ($manager->isBypassed()) {
            return;
        }

        $tenantId = $manager->id();

        if ($tenantId === null) {
            throw TenantNotResolvedException::forQuery($model::class);
        }

        $builder->where($model->qualifyColumn('tenant_id'), $tenantId);
    }
}
