<?php

namespace App\Support\Audit;

use App\Models\ActivityLog;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenant\TenantManager;
use Illuminate\Database\Eloquent\Model;

/**
 * Records an audit entry. The tenant defaults to the active one and the actor
 * to the authenticated user, so call sites usually only pass an action, a
 * human description and the affected subject.
 */
final class ActivityLogger
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function log(
        string $action,
        string $description,
        ?Model $subject = null,
        array $meta = [],
        ?Tenant $tenant = null,
        ?User $user = null,
        ?string $ipAddress = null,
    ): ActivityLog {
        return ActivityLog::query()->create([
            'tenant_id' => $tenant?->getKey() ?? app(TenantManager::class)->id(),
            'user_id' => ($user ?? auth()->user())?->getKey(),
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'meta' => $meta !== [] ? $meta : null,
            'ip_address' => $ipAddress ?? request()->ip(),
        ]);
    }
}
