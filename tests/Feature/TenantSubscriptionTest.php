<?php

use App\Filament\App\Pages\Subscription;
use App\Models\Tenant;
use App\Modules\Tenancy\Actions\RegisterTenantAction;
use Livewire\Livewire;

beforeEach(function () {
    $result = app(RegisterTenantAction::class)->execute([
        'cafe_name' => 'Kopi Langganan',
        'name' => 'Owner Langganan',
        'email' => 'owner@langganan.test',
        'password' => 'password123',
    ]);

    $this->owner = $result['owner'];
    $this->tenant = $result['tenant'];
});

it('allows an active trial tenant into the back office', function () {
    expect($this->tenant->onTrial())->toBeTrue();

    $this->actingAs($this->owner)->get('/app')->assertSuccessful();
});

it('redirects an expired trial to the subscription page', function () {
    $this->tenant->update([
        'status' => Tenant::STATUS_TRIAL,
        'trial_ends_at' => now()->subDay(),
    ]);

    $this->actingAs($this->owner)
        ->get('/app')
        ->assertRedirect(route('filament.app.pages.subscription'));
});

it('redirects a suspended tenant to the subscription page', function () {
    $this->tenant->update(['status' => Tenant::STATUS_SUSPENDED]);

    $this->actingAs($this->owner)
        ->get('/app/products')
        ->assertRedirect(route('filament.app.pages.subscription'));
});

it('still renders the subscription page for a blocked tenant', function () {
    $this->tenant->update(['status' => Tenant::STATUS_SUSPENDED]);

    $this->actingAs($this->owner)
        ->get('/app/subscription')
        ->assertSuccessful()
        ->assertSee('Ditangguhkan');
});

it('exposes and regenerates the tenant API key', function () {
    $original = $this->tenant->api_key;

    expect($original)->toStartWith('sk_');

    Livewire::actingAs($this->owner)
        ->test(Subscription::class)
        ->call('regenerateApiKey')
        ->assertHasNoErrors();

    expect($this->tenant->fresh()->api_key)->not->toBe($original);
});
