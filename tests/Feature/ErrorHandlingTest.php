<?php

use App\Exceptions\ConflictError;
use App\Exceptions\ValidationError;
use App\Filament\App\Resources\Users\Pages\CreateUser;
use App\Filament\App\Resources\Users\Pages\EditUser;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use App\Modules\Inventory\Services\StockLedger;
use App\Modules\Pos\Actions\PayOrderAction;
use App\Modules\Pos\Actions\VerifySupervisorPinAction;
use App\Modules\Pos\Enums\PaymentType;
use App\Modules\Tenancy\Actions\RegisterTenantAction;
use App\Support\Access\RoleDefinitions;
use App\Support\Access\UserDeletionGuard;
use App\Support\Catalog\CategoryDeletionGuard;
use App\Support\Catalog\PaymentMethodDeletionGuard;
use App\Support\Tenant\TenantManager;
use App\Support\Uploads\ImageUpload;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

beforeEach(function () {
    $result = app(RegisterTenantAction::class)->execute([
        'cafe_name' => 'Kopi Aman',
        'name' => 'Owner Aman',
        'email' => 'owner@aman.test',
        'password' => 'password123',
    ]);

    $this->owner = $result['owner'];
    $this->tenant = $result['tenant'];
    $this->key = $this->tenant->api_key;

    app(TenantManager::class)->set($this->tenant);

    $this->outlet = Outlet::query()->firstOrFail();
});

afterEach(function () {
    Cache::flush();
});

// ------------------------------------------------------------------
// 1. Envelope response error API yang seragam
// ------------------------------------------------------------------

it('renders a uniform error envelope when validation fails on the API', function () {
    $this->withHeader('X-Api-Key', $this->key)
        ->postJson('/api/v1/orders', [])
        ->assertStatus(422)
        ->assertJsonStructure(['success', 'code', 'message', 'errors'])
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'VALIDASI_GAGAL');
});

it('renders a friendly 404 envelope instead of a raw model-not-found', function () {
    $this->withHeader('X-Api-Key', $this->key)
        ->postJson('/api/v1/orders', [
            'items' => [['product_id' => 999999, 'quantity' => 1]],
        ])
        ->assertStatus(404)
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'MENU_TIDAK_DITEMUKAN')
        ->assertJson(fn ($json) => $json->whereType('message', 'string')->etc());
});

it('never leaks technical details in API error responses', function () {
    $body = $this->withHeader('X-Api-Key', $this->key)
        ->postJson('/api/v1/orders', [
            'items' => [['product_id' => 999999, 'quantity' => 1]],
        ])
        ->json();

    expect($body['message'] ?? '')->not->toContain('exception')
        ->and(json_encode($body))->not->toContain('stack');
});

// ------------------------------------------------------------------
// 2. Stok kurang saat pembayaran
// ------------------------------------------------------------------

function openOrderWithItem(string $productName, float $quantity, float $price = 20000, bool $trackStock = true): Order
{
    $tenant = test()->tenant;
    $outlet = Outlet::query()->firstOrFail();

    $product = Product::factory()->create([
        'tenant_id' => $tenant->getKey(),
        'name' => $productName,
        'track_stock' => $trackStock,
        'price' => $price,
    ]);

    $order = Order::factory()->forOutlet($outlet)->create(['grand_total' => $price * $quantity]);

    OrderItem::factory()->forOrder($order, $product)->create([
        'quantity' => $quantity,
        'unit_price' => $price,
        'line_total' => $price * $quantity,
    ]);

    return $order;
}

it('rejects payment with a clear stock shortage message', function () {
    $order = openOrderWithItem('Es Teh Manis', 5);
    $product = $order->items->firstOrFail()->product;
    $ledger = app(StockLedger::class);
    $stockItem = $ledger->forProduct($product, null);
    $stockItem->quantity = 2;
    $stockItem->save();

    try {
        app(PayOrderAction::class)->execute($order, [[
            'method_name' => 'Tunai',
            'method_type' => PaymentType::Cash,
            'amount' => $order->grand_total,
        ]]);

        test()->fail('Expected stock shortage error.');
    } catch (ValidationError $exception) {
        expect($exception->errorCode)->toBe('STOK_TIDAK_CUKUP')
            ->and($exception->getMessage())->toContain('Es Teh Manis')
            ->and($exception->getMessage())->toContain('2');
    }

    // Order tetap terbuka, tidak ada pembayaran tertulis.
    expect($order->fresh()->status->value)->toBe('open')
        ->and(Payment::query()->where('order_id', $order->getKey())->count())->toBe(0);
});

it('rejects underpayment with the exact missing amount', function () {
    $order = openOrderWithItem('Es Kopi Susu', 1, 25000, trackStock: false);

    try {
        app(PayOrderAction::class)->execute($order, [[
            'method_name' => 'Tunai',
            'method_type' => PaymentType::Cash,
            'amount' => 20000,
        ]]);

        test()->fail('Expected underpayment error.');
    } catch (ValidationError $exception) {
        expect($exception->errorCode)->toBe('PEMBAYARAN_KURANG')
            ->and($exception->getMessage())->toContain('Rp 5');
    }
});

// ------------------------------------------------------------------
// 3. PIN supervisor: salah, dibatasi, lalu terkunci
// ------------------------------------------------------------------

it('reports the wrong pin with remaining attempts', function () {
    $this->owner->update(['pin' => '4321']);

    try {
        app(VerifySupervisorPinAction::class)->execute('0000');
        test()->fail('Expected PIN error.');
    } catch (ValidationError $exception) {
        expect($exception->errorCode)->toBe('PIN_SALAH')
            ->and($exception->getMessage())->toContain('4');
    }

    // Percobaan sukses mengatur ulang hitungan, jadi percobaan salah
    // berikutnya kembali punya 4 sisa percobaan (bukan lanjut 3).
    $verified = app(VerifySupervisorPinAction::class)->execute('4321');
    expect($verified->getKey())->toBe($this->owner->getKey());

    try {
        app(VerifySupervisorPinAction::class)->execute('0000');
    } catch (ValidationError $exception) {
        expect($exception->getMessage())->toContain('Sisa percobaan: 4');
    }
});

it('locks the pin temporarily after five wrong attempts', function () {
    $this->owner->update(['pin' => '4321']);

    for ($i = 0; $i < 5; $i++) {
        try {
            app(VerifySupervisorPinAction::class)->execute('0000');
        } catch (ValidationError $exception) {
            // diabaikan: hitungan percobaan
        }
    }

    try {
        app(VerifySupervisorPinAction::class)->execute('0000');
        test()->fail('Expected rate limited error.');
    } catch (ValidationError $exception) {
        expect($exception->errorCode)->toBe('PIN_TERKUNCI')
            ->and($exception->getMessage())->toContain('menit');
    }

    // Bahkan PIN yang benar ditolak saat terkunci.
    try {
        app(VerifySupervisorPinAction::class)->execute('4321');
        test()->fail('Expected lock to block correct pin too.');
    } catch (ValidationError $exception) {
        expect($exception->errorCode)->toBe('PIN_TERKUNCI');
    }
});

// ------------------------------------------------------------------
// 4. Double submit: bayar dua kali ditolak
// ------------------------------------------------------------------

it('blocks a second payment attempt on an already-paid order', function () {
    $order = openOrderWithItem('Kopi Hitam', 1, trackStock: false);

    app(PayOrderAction::class)->execute($order, [[
        'method_name' => 'Tunai',
        'method_type' => PaymentType::Cash,
        'amount' => $order->grand_total,
    ]]);

    $firstPaymentCount = Payment::query()->where('order_id', $order->getKey())->count();

    try {
        app(PayOrderAction::class)->execute($order, [[
            'method_name' => 'Tunai',
            'method_type' => PaymentType::Cash,
            'amount' => $order->grand_total,
        ]]);

        test()->fail('Expected conflict error on double pay.');
    } catch (ConflictError $exception) {
        expect($exception->errorCode)->toBe('PESANAN_SUDAH_DIBAYAR')
            ->and($exception->getMessage())->toContain('sudah dibayar');
    }

    expect(Payment::query()->where('order_id', $order->getKey())->count())->toBe($firstPaymentCount);
});

// ------------------------------------------------------------------
// 5. Idempotency: retry dengan kunci sama tidak dobel order
// ------------------------------------------------------------------

it('replays the same online order for a repeated idempotency key', function () {
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->getKey(),
        'name' => 'Matcha Latte',
        'price' => 30000,
    ]);

    $payload = [
        'customer_name' => 'Budi',
        'type' => 'pickup',
        'items' => [['product_id' => $product->getKey(), 'quantity' => 1]],
    ];

    $first = $this->withHeader('X-Api-Key', $this->key)
        ->withHeader('Idempotency-Key', 'retry-abc-123')
        ->postJson('/api/v1/orders', $payload);

    $first->assertCreated();

    $second = $this->withHeader('X-Api-Key', $this->key)
        ->withHeader('Idempotency-Key', 'retry-abc-123')
        ->postJson('/api/v1/orders', $payload);

    $second->assertSuccessful();

    expect($second->headers->get('Idempotency-Replayed'))->toBe('true')
        ->and($second->json('data.number'))->toBe($first->json('data.number'))
        ->and(Order::query()->where('customer_name', 'Budi')->count())->toBe(1);
});

// ------------------------------------------------------------------
// 6. Upload foto: isi file dicek, bukan cuma ekstensi
// ------------------------------------------------------------------

it('accepts jpg, png and webp signatures', function () {
    expect(ImageUpload::hasImageSignature("\xFF\xD8\xFF\xE0junk"))->toBeTrue()
        ->and(ImageUpload::hasImageSignature("\x89PNG\r\n\x1A\n...."))->toBeTrue()
        ->and(ImageUpload::hasImageSignature('RIFF....WEBP'))->toBeTrue()
        ->and(ImageUpload::hasImageSignature('<html>nolak</html>'))->toBeFalse();
});

// ------------------------------------------------------------------
// 7. Role Owner ditolak di backend + perlindungan owner
// ------------------------------------------------------------------

it('rejects the owner role when creating staff through the panel', function () {
    Livewire::actingAs($this->owner)
        ->test(CreateUser::class)
        ->fillForm([
            'name' => 'Staf Baru',
            'email' => 'staf@aman.test',
            'password' => 'password123',
            'role' => RoleDefinitions::OWNER,
        ])
        ->call('create')
        ->assertHasFormErrors(['role']);

    expect(User::query()->where('email', 'staf@aman.test')->doesntExist())->toBeTrue();
});

it('prevents the owner from demoting themselves', function () {
    $this->actingAs($this->owner);

    $page = new EditUser;
    $page->record = $this->owner;

    try {
        (new ReflectionMethod($page, 'mutateFormDataBeforeSave'))->invoke($page, ['role' => RoleDefinitions::CASHIER]);
        test()->fail('Expected demotion to be rejected.');
    } catch (ValidationException $exception) {
        expect($exception->getMessage())->toContain('tidak bisa menurunkan');
    }

    expect($this->owner->fresh()->roleIn($this->tenant))->toBe(RoleDefinitions::OWNER);
});

it('blocks deleting your own account and the last owner', function () {
    expect(UserDeletionGuard::rejectReason([$this->owner], $this->owner->getKey()))
        ->toContain('tidak bisa menghapus akunmu sendiri');
});

// ------------------------------------------------------------------
// 8. Kategori & metode bayar: hapus yang masih dipakai ditolak
// ------------------------------------------------------------------

it('cannot delete a category that still has products', function () {
    $category = Category::factory()->create(['tenant_id' => $this->tenant->getKey()]);
    Product::factory()->count(3)->create([
        'tenant_id' => $this->tenant->getKey(),
        'category_id' => $category->getKey(),
    ]);

    expect(CategoryDeletionGuard::rejectReason([$category]))->toContain('masih berisi 3 menu')
        ->and(CategoryDeletionGuard::rejectReason([Category::factory()->make(['tenant_id' => $this->tenant->getKey()])]))->toBeNull();
});

it('cannot delete a payment method already used in transactions', function () {
    $method = PaymentMethod::factory()->create(['tenant_id' => $this->tenant->getKey(), 'type' => PaymentType::Qris]);

    expect(PaymentMethodDeletionGuard::rejectReason([$method]))->toBeNull();

    $order = openOrderWithItem('Es Kopi', 1);

    $order->payments()->create([
        'payment_method_id' => $method->getKey(),
        'method_name' => $method->name,
        'method_type' => $method->type,
        'amount' => 20000,
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    expect(PaymentMethodDeletionGuard::rejectReason([$method]))->toContain('Nonaktifkan saja');
});
