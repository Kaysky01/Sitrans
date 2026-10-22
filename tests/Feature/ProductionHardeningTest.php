<?php

use App\Models\Tenant;
use App\Modules\Tenancy\Actions\RegisterTenantAction;

beforeEach(function () {
    $result = app(RegisterTenantAction::class)->execute([
        'cafe_name' => 'Kopi Hardening',
        'name' => 'Owner Hardening',
        'email' => 'owner@hardening.test',
        'password' => 'password123',
    ]);

    $this->owner = $result['owner'];
    $this->tenant = $result['tenant'];
    $this->outlet = $result['outlet'];
});

it('renders landing page with status 200', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('Sitrans')
        ->assertSee('POS');
});

it('renders custom 404 error page for non-existent routes', function () {
    $response = $this->get('/halaman-ini-pasti-tidak-ada-12345');

    $response->assertNotFound()
        ->assertSee('404')
        ->assertSee('Halaman tidak ditemukan');
});

it('redirects inactive or suspended tenant away from POS', function () {
    $this->tenant->update([
        'status' => Tenant::STATUS_SUSPENDED,
        'trial_ends_at' => now()->subDay(),
    ]);

    $response = $this->actingAs($this->owner)
        ->withSession(['active_tenant_id' => $this->tenant->id])
        ->get('/pos');

    $response->assertRedirect(route('filament.app.pages.subscription'));
});

it('allows active tenant with permission to access POS', function () {
    $response = $this->actingAs($this->owner)
        ->withSession(['active_tenant_id' => $this->tenant->id])
        ->get('/pos');

    $response->assertOk();
});
