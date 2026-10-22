<?php

use App\Models\User;
use App\Modules\Tenancy\Actions\RegisterTenantAction;

beforeEach(function () {
    $result = app(RegisterTenantAction::class)->execute([
        'cafe_name' => 'Kopi Uji',
        'name' => 'Owner Uji',
        'email' => 'owner@uji.test',
        'password' => 'password123',
    ]);

    /** @var User $owner */
    $this->owner = $result['owner'];
});

it('renders every back office page for the tenant owner', function (string $path) {
    $this->actingAs($this->owner)
        ->get($path)
        ->assertSuccessful();
})->with([
    '/app',
    '/app/products',
    '/app/products/create',
    '/app/categories',
    '/app/categories/create',
    '/app/modifier-groups',
    '/app/modifier-groups/create',
    '/app/bundles',
    '/app/bundles/create',
    '/app/outlets',
    '/app/outlets/create',
    '/app/payment-methods',
    '/app/payment-methods/create',
    '/app/users',
    '/app/users/create',
    '/app/orders',
    '/app/stock-items',
    '/app/sales-report',
    '/app/subscription',
    '/app/manage-settings',
]);
