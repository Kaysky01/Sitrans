<?php

use App\Filament\App\Pages\Subscription;
use App\Filament\App\Resources\PaymentMethods\Pages\CreatePaymentMethod;
use App\Filament\App\Resources\Users\UserResource;
use App\Models\PaymentMethod;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Pos\Enums\PaymentType;
use App\Modules\Tenancy\Actions\RegisterTenantAction;
use App\Support\Access\RoleDefinitions;
use App\Support\Tenant\TenantManager;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    $result = app(RegisterTenantAction::class)->execute([
        'cafe_name' => 'Kopi Staf',
        'name' => 'Owner Staf',
        'email' => 'owner@staf.test',
        'password' => 'password123',
    ]);

    /** @var User $owner */
    $this->owner = $result['owner'];
    $this->tenant = $result['tenant'];

    app(TenantManager::class)->set($this->tenant);
});

function staffMember(string $role): User
{
    $tenant = test()->tenant;

    $user = User::factory()->create(['last_active_tenant_id' => $tenant->getKey()]);

    $user->tenants()->attach($tenant->getKey(), [
        'role' => $role,
        'is_active' => true,
        'joined_at' => now(),
    ]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getKey());
    $user->assignRole($role);

    return $user;
}

it('membuat kode metode pembayaran otomatis dari nama', function () {
    $first = PaymentMethod::create([
        'name' => 'QRIS Toko',
        'type' => PaymentType::Qris,
        'requires_reference' => true,
        'is_active' => true,
    ]);

    $second = PaymentMethod::create([
        'name' => 'QRIS Toko',
        'type' => PaymentType::Qris,
        'requires_reference' => true,
        'is_active' => true,
    ]);

    expect($first->code)->toBe('qris_toko')
        ->and($second->code)->toBe('qris_toko_2')
        ->and($first->tenant_id)->toBe($this->tenant->getKey());
});

it('menyimpan metode bayar dengan kode kosong secara otomatis', function () {
    Livewire::actingAs($this->owner)
        ->test(CreatePaymentMethod::class)
        ->fillForm([
            'name' => 'QRIS Meja',
            'type' => PaymentType::Qris->value,
            'code' => '',
            'requires_reference' => true,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $method = PaymentMethod::query()->where('name', 'QRIS Meja')->first();

    expect($method)->not->toBeNull()
        ->and($method->code)->toBe('qris_meja')
        ->and($method->type)->toBe(PaymentType::Qris);
});

it('menyembunyikan akun pemilik dari aksi kelola staf', function () {
    $this->actingAs($this->owner);

    expect(UserResource::canEdit($this->owner))->toBeFalse()
        ->and(UserResource::canDelete($this->owner))->toBeFalse()
        ->and(UserResource::canCreate())->toBeTrue();
});

it('hanya menampilkan halaman langganan untuk pemilik saat langganan aktif', function () {
    $supervisor = staffMember(RoleDefinitions::MANAGER);

    $this->actingAs($this->owner);
    expect(Subscription::canAccess())->toBeTrue();

    $this->actingAs($supervisor);
    expect(Subscription::canAccess())->toBeFalse();

    $this->actingAs($this->owner)
        ->get('/app/subscription')
        ->assertSuccessful();
});

it('tetap membuka halaman langganan saat masa aktif berakhir', function () {
    $supervisor = staffMember(RoleDefinitions::MANAGER);
    $this->tenant->update(['status' => Tenant::STATUS_SUSPENDED]);

    $this->actingAs($supervisor)
        ->get('/app/subscription')
        ->assertSuccessful();
});
