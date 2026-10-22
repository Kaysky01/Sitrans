<?php

namespace App\Models;

use App\Support\Tenant\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['outlet_id', 'header', 'footer', 'show_logo', 'show_tax', 'show_cashier', 'paper_width'])]
class ReceiptSetting extends Model
{
    use BelongsToTenant;

    protected function casts(): array
    {
        return [
            'show_logo' => 'boolean',
            'show_tax' => 'boolean',
            'show_cashier' => 'boolean',
            'paper_width' => 'integer',
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
