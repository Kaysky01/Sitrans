<?php

use App\Filament\Admin\Resources\Tenants\Pages\CreateTenant;
use App\Filament\Admin\Resources\Tenants\Pages\ListTenants;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Tenancy\Actions\RegisterTenantAction;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    $this->superAdmin = User::factory()->superAdmin()->create();

    Filament::setCurrentPanel('admin');

    $result = app(RegisterTenantAction::class)->execute([
        'cafe_name' => 'Kopi Kelola',
        'name' => 'Owner Kelola',
        'email' => 'owner@kelola.test',
        'password' => 'password123',
    ]);

    $this->tenant = $result['tenant'];
    $this->owner = $result['owner'];
});

it('lets a super admin view the platform dashboard', function () {
    $this->actingAs($this->superAdmin)->get('/admin')->assertSuccessful();
});

it('lists every tenant to the super admin', function () {
    $this->actingAs($this->superAdmin)
        ->get('/admin/tenants')
        ->assertSuccessful()
        ->assertSee('Kopi Kelola');
});

it('blocks a tenant owner from the admin panel', function () {
    $this->actingAs($this->owner)->get('/admin')->assertForbidden();
});

it('blocks a super admin from the tenant panel', function () {
    $this->actingAs($this->superAdmin)->get('/app')->assertForbidden();
});

it('suspends and reactivates a tenant from the table', function () {
    Livewire::actingAs($this->superAdmin)
        ->test(ListTenants::class)
        ->callTableAction('suspend', $this->tenant);

    expect($this->tenant->fresh()->status)->toBe(Tenant::STATUS_SUSPENDED);

    Livewire::actingAs($this->superAdmin)
        ->test(ListTenants::class)
        ->callTableAction('activate', $this->tenant->fresh());

    expect($this->tenant->fresh()->status)->toBe(Tenant::STATUS_ACTIVE);
});

it('extends a tenant trial by the configured window', function () {
    $this->tenant->update(['status' => Tenant::STATUS_SUSPENDED, 'trial_ends_at' => now()->subDay()]);

    Livewire::actingAs($this->superAdmin)
        ->test(ListTenants::class)
        ->callTableAction('extend', $this->tenant);

    $tenant = $this->tenant->fresh();

    expect($tenant->status)->toBe(Tenant::STATUS_TRIAL)
        ->and($tenant->trial_ends_at->isFuture())->toBeTrue();
});

it('creates a tenant and its owner through the admin panel', function () {
    Livewire::actingAs($this->superAdmin)
        ->test(CreateTenant::class)
        ->fillForm([
            'name' => 'Warung Baru',
            'email' => 'halo@warung.test',
            'status' => Tenant::STATUS_ACTIVE,
            'owner_name' => 'Pemilik Warung',
            'owner_email' => 'pemilik@warung.test',
            'owner_password' => 'password123',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $tenant = Tenant::query()->where('name', 'Warung Baru')->firstOrFail();
    $owner = User::query()->where('email', 'pemilik@warung.test')->firstOrFail();

    expect($tenant->status)->toBe(Tenant::STATUS_ACTIVE)
        ->and($owner->tenants()->whereKey($tenant->getKey())->exists())->toBeTrue();
});
