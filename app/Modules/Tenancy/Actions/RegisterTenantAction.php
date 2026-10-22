<?php

namespace App\Modules\Tenancy\Actions;

use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\ReceiptSetting;
use App\Models\TaxSetting;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Pos\Enums\PaymentType;
use App\Support\Access\RoleDefinitions;
use App\Support\Tenant\TenantManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

/**
 * Self-service registration: creates a tenant on a 14-day trial, its first
 * outlet, default settings, payment methods, the owner user (tenant member)
 * and the tenant's roles.
 */
final class RegisterTenantAction
{
    public function __construct(private readonly SetupTenantRolesAction $setupRoles) {}

    /**
     * @param  array{cafe_name: string, name: string, email: string, phone?: string|null, password: string, outlet_name?: string|null}  $data
     * @return array{tenant: Tenant, owner: User, outlet: Outlet}
     */
    public function execute(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $tenant = Tenant::create([
                'name' => $data['cafe_name'],
                'slug' => $this->uniqueSlug($data['cafe_name']),
                'api_key' => $this->uniqueApiKey(),
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'status' => Tenant::STATUS_TRIAL,
                'trial_ends_at' => now()->addDays(config('sitrans.trial_days')),
                'settings' => [],
            ]);

            $manager = app(TenantManager::class);
            $previous = $manager->current();
            $manager->set($tenant);

            try {
                $outlet = Outlet::create([
                    'name' => $data['outlet_name'] ?? $data['cafe_name'],
                    'is_active' => true,
                ]);

                TaxSetting::create([
                    'outlet_id' => null,
                    'pb1_enabled' => true,
                    'pb1_rate' => config('sitrans.default_pb1_rate'),
                    'service_enabled' => false,
                    'service_rate' => config('sitrans.default_service_rate'),
                    'prices_include_tax' => false,
                ]);

                ReceiptSetting::create([
                    'outlet_id' => null,
                    'header' => $data['cafe_name'],
                    'footer' => 'Terima kasih atas kunjungan Anda',
                ]);

                $this->seedPaymentMethods();

                $owner = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'password' => $data['password'],
                    'last_active_tenant_id' => $tenant->getKey(),
                ]);

                $owner->tenants()->attach($tenant, [
                    'role' => RoleDefinitions::OWNER,
                    'is_active' => true,
                    'joined_at' => now(),
                ]);
            } finally {
                if ($previous !== null) {
                    $manager->set($previous);
                } else {
                    $manager->forget();
                }
            }

            $this->setupRoles->execute($tenant);

            app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getKey());
            $owner->assignRole(RoleDefinitions::OWNER);

            return ['tenant' => $tenant, 'owner' => $owner, 'outlet' => $outlet];
        });
    }

    private function seedPaymentMethods(): void
    {
        $methods = [
            ['name' => 'Tunai', 'code' => 'cash', 'type' => PaymentType::Cash, 'requires_reference' => false, 'sort_order' => 1],
            ['name' => 'QRIS', 'code' => 'qris', 'type' => PaymentType::Qris, 'requires_reference' => true, 'sort_order' => 2],
            ['name' => 'Transfer', 'code' => 'transfer', 'type' => PaymentType::Transfer, 'requires_reference' => true, 'sort_order' => 3],
        ];

        foreach ($methods as $method) {
            PaymentMethod::create($method + ['is_active' => true]);
        }
    }

    private function uniqueApiKey(): string
    {
        do {
            $key = 'sk_'.Str::random(48);
        } while (Tenant::withTrashed()->where('api_key', $key)->exists());

        return $key;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'kafe';
        $slug = $base;
        $suffix = 1;

        while (Tenant::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$suffix);
        }

        return $slug;
    }
}
