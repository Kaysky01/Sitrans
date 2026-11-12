<?php

namespace App\Models;

use App\Modules\Pos\Enums\DiscountType;
use App\Modules\Pos\Enums\OrderChannel;
use App\Modules\Pos\Enums\OrderFulfillmentStatus;
use App\Modules\Pos\Enums\OrderStatus;
use App\Modules\Pos\Enums\OrderType;
use App\Support\Tenant\BelongsToTenant;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'outlet_id', 'cash_shift_id', 'user_id', 'number', 'channel', 'type', 'status',
    'fulfillment_status', 'customer_name', 'table_number',
    'subtotal', 'discount_total', 'discount_type', 'discount_value',
    'tax_amount', 'service_amount', 'grand_total', 'paid_total', 'change_total',
    'notes', 'ordered_at', 'paid_at', 'accepted_at', 'processing_started_at', 'fulfilled_at',
    'voided_by', 'voided_at', 'void_reason',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'channel' => OrderChannel::class,
            'type' => OrderType::class,
            'status' => OrderStatus::class,
            'fulfillment_status' => OrderFulfillmentStatus::class,
            'discount_type' => DiscountType::class,
            'subtotal' => 'float',
            'discount_total' => 'float',
            'discount_value' => 'float',
            'tax_amount' => 'float',
            'service_amount' => 'float',
            'grand_total' => 'float',
            'paid_total' => 'float',
            'change_total' => 'float',
            'ordered_at' => 'datetime',
            'paid_at' => 'datetime',
            'accepted_at' => 'datetime',
            'processing_started_at' => 'datetime',
            'fulfilled_at' => 'datetime',
            'voided_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Outlet, $this>
     */
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<CashShift, $this>
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(CashShift::class, 'cash_shift_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isPaid(): bool
    {
        return in_array($this->status, [OrderStatus::Paid, OrderStatus::Completed], true);
    }

    public function isVoided(): bool
    {
        return $this->status === OrderStatus::Voided;
    }

    /**
     * Pesanan masih berjalan dan perlu ditangani staf.
     */
    public function isActiveForFulfillment(): bool
    {
        return ! $this->isVoided()
            && $this->status !== OrderStatus::Refunded
            && $this->fulfillment_status !== OrderFulfillmentStatus::Completed;
    }

    /**
     * @param  Builder<Order>  $query
     */
    public function scopeActiveFulfillment(Builder $query): void
    {
        $query->whereNotIn('status', [OrderStatus::Voided, OrderStatus::Refunded])
            ->where('fulfillment_status', '!=', OrderFulfillmentStatus::Completed);
    }

    /**
     * Orders that count as revenue.
     *
     * @param  Builder<Order>  $query
     */
    public function scopeRevenue(Builder $query): void
    {
        $query->whereIn('status', OrderStatus::revenueBearing());
    }

    /**
     * @param  Builder<Order>  $query
     */
    public function scopePaidBetween(Builder $query, mixed $from, mixed $to): void
    {
        $query->revenue()->whereBetween('paid_at', [$from, $to]);
    }
}
