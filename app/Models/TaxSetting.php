<?php

namespace App\Models;

use App\Support\Tenant\BelongsToTenant;
use Database\Factories\TaxSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['outlet_id', 'pb1_enabled', 'pb1_rate', 'service_enabled', 'service_rate', 'prices_include_tax'])]
class TaxSetting extends Model
{
    /** @use HasFactory<TaxSettingFactory> */
    use BelongsToTenant, HasFactory;

    protected function casts(): array
    {
        return [
            'pb1_enabled' => 'boolean',
            'pb1_rate' => 'float',
            'service_enabled' => 'boolean',
            'service_rate' => 'float',
            'prices_include_tax' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Outlet, $this>
     */
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }
}
