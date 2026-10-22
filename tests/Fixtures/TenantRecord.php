<?php

namespace Tests\Fixtures;

use App\Support\Tenant\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Minimal tenant-owned model used purely to exercise the tenancy machinery
 * (global scope, auto-fill, isolation) before real domain tables exist.
 */
class TenantRecord extends Model
{
    use BelongsToTenant;

    protected $table = 'tenant_records';

    protected $guarded = [];
}
