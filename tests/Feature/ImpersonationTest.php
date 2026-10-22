<?php

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Tenancy\Actions\RegisterTenantAction;
use App\Support\Tenant\TenantManager;

beforeEach(function () {
    $result = app(RegisterTenantAction::class)->execute([
        'cafe_name' => 'Kopi Impersonasi',
        'name' => 'Owner Kafe',
        'email' => 'owner@kafe.test',
        'password' => 'password123',
    ]);

    $this->owner = $result['owner'];
    $this->tenant = $result['tenant'];

    $this->admin = User::factory()->create(['is_super_admin' => true]);
});

it('lets a super admin step into a tenant', function () {
    $this->actingAs($this->admin)
        ->get(route('impersonate.start', $this->tenant))
        ->assertRedirect(route('filament.app.pages.dashboard'))
        ->assertSessionHas('impersonator_id', $this->admin->getKey())
        ->assertSessionHas('active_tenant_id', $this->tenant->getKey());

    $this->assertAuthenticatedAs($this->owner);
});

it('forbids a non super admin from impersonating', function () {
    $this->actingAs($this->owner)
        ->get(route('impersonate.start', $this->tenant))
        ->assertForbidden();

    $this->assertAuthenticatedAs($this->owner);
});

it('returns to the admin account when stopping', function () {
    $this->actingAs($this->owner)
        ->withSession(['impersonator_id' => $this->admin->getKey(), 'active_tenant_id' => $this->tenant->getKey()])
        ->post(route('impersonate.stop'))
        ->assertRedirect(route('filament.admin.pages.dashboard'))
        ->assertSessionMissing('impersonator_id');

    $this->assertAuthenticatedAs($this->admin);
});

it('refuses to impersonate a tenant without active users', function () {
    $empty = Tenant::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('impersonate.start', $empty))
        ->assertRedirect(route('filament.admin.resources.tenants.index'))
        ->assertSessionHas('impersonation_error');

    $this->assertAuthenticatedAs($this->admin);
});

it('keeps the impersonated user signed in on the next request', function () {
    $this->actingAs($this->admin)->get(route('impersonate.start', $this->tenant));

    $this->get(route('filament.app.pages.dashboard'))->assertSuccessful();

    $this->assertAuthenticatedAs($this->owner);
    expect(app(TenantManager::class)->current()?->getKey())->toBe($this->tenant->getKey());
});
