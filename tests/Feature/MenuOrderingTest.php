<?php

use App\Filament\App\Resources\Categories\Pages\ListCategories;
use App\Filament\App\Resources\Products\Pages\ListProducts;
use App\Livewire\Pos\Cashier;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Modules\Tenancy\Actions\RegisterTenantAction;
use App\Support\Tenant\TenantManager;
use Livewire\Livewire;

beforeEach(function () {
    $result = app(RegisterTenantAction::class)->execute([
        'cafe_name' => 'Kopi Urut',
        'name' => 'Owner Urut',
        'email' => 'owner@urut.test',
        'password' => 'password123',
    ]);

    /** @var User $owner */
    $this->owner = $result['owner'];
    $this->tenant = $result['tenant'];

    app(TenantManager::class)->set($this->tenant);
});

it('menampilkan kategori dan menu dengan urutan yang sama di pos dan admin', function () {
    $secondCategory = Category::factory()->create([
        'tenant_id' => $this->tenant->getKey(),
        'name' => 'Teh',
        'sort_order' => 2,
    ]);
    $firstCategory = Category::factory()->create([
        'tenant_id' => $this->tenant->getKey(),
        'name' => 'Kopi',
        'sort_order' => 1,
    ]);

    $secondProduct = Product::factory()->inCategory($firstCategory)->create([
        'name' => 'Menu Zeta',
        'sort_order' => 2,
    ]);
    $firstProduct = Product::factory()->inCategory($firstCategory)->create([
        'name' => 'Menu Alpha',
        'sort_order' => 1,
    ]);

    Livewire::actingAs($this->owner)
        ->test(Cashier::class)
        ->assertSeeInOrder([$firstCategory->name, $secondCategory->name])
        ->assertSeeInOrder([$firstProduct->name, $secondProduct->name]);

    Livewire::actingAs($this->owner)
        ->test(ListCategories::class)
        ->assertCanSeeTableRecords([$firstCategory, $secondCategory], inOrder: true);

    Livewire::actingAs($this->owner)
        ->test(ListProducts::class)
        ->assertCanSeeTableRecords([$firstProduct, $secondProduct], inOrder: true);
});

it('mengurutkan nama menu sebagai penentu saat sort order sama', function () {
    $category = Category::factory()->create([
        'tenant_id' => $this->tenant->getKey(),
        'sort_order' => 1,
    ]);

    $later = Product::factory()->inCategory($category)->create([
        'name' => 'Zeta',
        'sort_order' => 5,
    ]);
    $earlier = Product::factory()->inCategory($category)->create([
        'name' => 'Alpha',
        'sort_order' => 5,
    ]);

    Livewire::actingAs($this->owner)
        ->test(Cashier::class)
        ->assertSeeInOrder([$earlier->name, $later->name]);

    Livewire::actingAs($this->owner)
        ->test(ListProducts::class)
        ->assertCanSeeTableRecords([$earlier, $later], inOrder: true);
});
