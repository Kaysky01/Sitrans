<?php

namespace App\Models;

use App\Support\Tenant\BelongsToTenant;
use Database\Factories\OutletFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'code', 'address', 'phone', 'timezone', 'is_active'])]
class Outlet extends Model
{
    /** @use HasFactory<OutletFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasOne<TaxSetting, $this>
     */
    public function taxSetting(): HasOne
    {
        return $this->hasOne(TaxSetting::class);
    }

    /**
     * @return HasOne<ReceiptSetting, $this>
     */
    public function receiptSetting(): HasOne
    {
        return $this->hasOne(ReceiptSetting::class);
    }
}
