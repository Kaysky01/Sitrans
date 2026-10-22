<?php

namespace App\Models;

use App\Support\Tenant\BelongsToTenant;
use Database\Factories\ActivityLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Append-only audit trail of meaningful actions (order voids, discounts,
 * inventory adjustments, settings changes, impersonation, ...). Entries carry
 * the tenant they happened in (null for platform-level events) plus the actor.
 */
#[Fillable(['tenant_id', 'user_id', 'action', 'description', 'subject_type', 'subject_id', 'meta', 'ip_address'])]
class ActivityLog extends Model
{
    /** @use HasFactory<ActivityLogFactory> */
    use BelongsToTenant, HasFactory;

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
