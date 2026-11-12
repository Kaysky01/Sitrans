<?php

namespace App\Models;

use App\Modules\Pos\Enums\PaymentType;
use App\Support\Tenant\BelongsToTenant;
use App\Support\Tenant\TenantManager;
use Database\Factories\PaymentMethodFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable(['name', 'code', 'type', 'requires_reference', 'is_active', 'sort_order'])]
class PaymentMethod extends Model
{
    /** @use HasFactory<PaymentMethodFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        // Kode opsional: buat otomatis dari nama agar metode baru langsung siap dipakai.
        static::creating(function (PaymentMethod $method): void {
            if (blank($method->code)) {
                $method->code = static::generateCode($method);
            }
        });
    }

    public static function generateCode(PaymentMethod $method): string
    {
        $base = Str::slug((string) $method->name, '_') ?: 'metode';
        $code = $base;
        $suffix = 1;
        $tenantId = $method->tenant_id ?? app(TenantManager::class)->id();

        while (static::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('code', $code)
            ->when($method->exists, fn ($query) => $query->whereKeyNot($method->getKey()))
            ->exists()
        ) {
            $code = $base.'_'.(++$suffix);
        }

        return $code;
    }

    protected function casts(): array
    {
        return [
            'type' => PaymentType::class,
            'requires_reference' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
