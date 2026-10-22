<?php

use App\Filament\App\Pages\ManageSettings;
use App\Filament\App\Pages\SalesReport;
use App\Filament\App\Pages\Subscription;
use App\Filament\App\Resources\Orders\OrderResource;
use App\Filament\App\Resources\Products\ProductResource;
use App\Filament\App\Resources\StockItems\StockItemResource;
use App\Filament\App\Resources\Users\UserResource;
use App\Filament\App\Widgets\SalesOverview;
use App\Http\Responses\Auth\LoginResponse;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Modules\Tenancy\Actions\RegisterTenantAction;
use App\Support\Access\RoleDefinitions;
use App\Support\Tenant\TenantManager;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    $result = app(RegisterTenantAction::class)->execute([
        'cafe_name' => 'Kopi Akses',
        'name' => 'Owner Akses',
        'email' => 'owner@akses.test',
        'password' => 'password123',
    ]);

    $this->owner = $result['owner'];
    $this->tenant = $result['tenant'];

    app(TenantManager::class)->set($this->tenant);
});

function staff(string $role): User
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

it('grants the owner access to every back office feature', function () {
    $this->actingAs($this->owner);

    expect(ProductResource::canViewAny())->toBeTrue()
        ->and(UserResource::canViewAny())->toBeTrue()
        ->and(OrderResource::canViewAny())->toBeTrue()
        ->and(ProductResource::canCreate())->toBeTrue()
        ->and(SalesReport::canAccess())->toBeTrue()
        ->and(ManageSettings::canAccess())->toBeTrue()
        ->and(Subscription::canAccess())->toBeTrue()
        ->and(SalesOverview::canView())->toBeTrue();
});

it('gives a cashier a read-only menu and orders, nothing else', function () {
    $this->actingAs(staff(RoleDefinitions::CASHIER));

    expect(ProductResource::canViewAny())->toBeTrue()
        ->and(ProductResource::canCreate())->toBeFalse()
        ->and(ProductResource::canEdit(new Product))->toBeFalse()
        ->and(OrderResource::canViewAny())->toBeTrue()
        ->and(StockItemResource::canViewAny())->toBeFalse()
        ->and(UserResource::canViewAny())->toBeFalse()
        ->and(SalesReport::canAccess())->toBeFalse()
        ->and(ManageSettings::canAccess())->toBeFalse()
        ->and(Subscription::canAccess())->toBeFalse()
        ->and(SalesOverview::canView())->toBeFalse();
});

it('lets a cashier see orders but not edit them', function () {
    $this->actingAs(staff(RoleDefinitions::CASHIER));

    expect(OrderResource::canViewAny())->toBeTrue()
        ->and(OrderResource::canView(new Order))->toBeTrue()
        ->and(OrderResource::canCreate())->toBeFalse()
        ->and(OrderResource::canEdit(new Order))->toBeFalse();
});

it('lets a manager run the menu but not the staff list', function () {
    $this->actingAs(staff(RoleDefinitions::MANAGER));

    expect(ProductResource::canViewAny())->toBeTrue()
        ->and(ProductResource::canEdit(new Product))->toBeTrue()
        ->and(UserResource::canViewAny())->toBeFalse();
});

it('lets a cashier browse the menu but not the staff or reports pages', function () {
    $cashier = staff(RoleDefinitions::CASHIER);

    $this->actingAs($cashier)->get('/app/products')->assertSuccessful();
    $this->actingAs($cashier)->get('/app/users')->assertForbidden();
    $this->actingAs($cashier)->get('/app/sales-report')->assertForbidden();
});

it('allows the owner into the products page over http', function () {
    $this->actingAs($this->owner)
        ->get('/app/products')
        ->assertSuccessful();
});

it('only lets cashiers use the point of sale', function () {
    $this->actingAs(staff(RoleDefinitions::CASHIER))
        ->get('/pos')
        ->assertSuccessful();

    $this->actingAs(staff(RoleDefinitions::KITCHEN))
        ->get('/pos')
        ->assertForbidden();
});

it('lands the owner on the back office dashboard after login', function () {
    $this->actingAs($this->owner);

    $response = app(LoginResponse::class)->toResponse(request());

    expect($response->getTargetUrl())->toBe(route('filament.app.pages.dashboard'));
});

it('lands the cashier straight on the point of sale after login', function () {
    $this->actingAs(staff(RoleDefinitions::CASHIER));

    $response = app(LoginResponse::class)->toResponse(request());

    expect($response->getTargetUrl())->toBe(route('pos'));
});

it('lands kitchen staff on the order history after login', function () {
    $this->actingAs(staff(RoleDefinitions::KITCHEN));

    $response = app(LoginResponse::class)->toResponse(request());

    expect($response->getTargetUrl())->toBe(OrderResource::getUrl('index'));
});

it('forwards the kasir sidebar shortcut to the point of sale', function () {
    $this->actingAs(staff(RoleDefinitions::CASHIER))
        ->get('/app/pos')
        ->assertRedirect(route('pos'));

    $this->actingAs(staff(RoleDefinitions::KITCHEN))
        ->get('/app/pos')
        ->assertForbidden();
});

it('renders the dashboard greeting and actions matching the role', function () {
    $this->actingAs(staff(RoleDefinitions::CASHIER));
    $this->get('/app')
        ->assertSuccessful()
        ->assertSee('Kasir &middot;', false)
        ->assertSee('Buka Kasir');

    $this->actingAs(staff(RoleDefinitions::KITCHEN));
    $this->get('/app')
        ->assertSuccessful()
        ->assertSee('Dapur &middot;', false)
        ->assertSee('Lihat Riwayat');
});
