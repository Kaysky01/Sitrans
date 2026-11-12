<?php

namespace App\Livewire\Pos;

use App\Models\Bundle;
use App\Models\CashShift;
use App\Models\Category;
use App\Models\ModifierGroup;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Modules\Pos\Actions\AddOrderItemAction;
use App\Modules\Pos\Actions\ApplyDiscountAction;
use App\Modules\Pos\Actions\CreateOrderAction;
use App\Modules\Pos\Actions\PayOrderAction;
use App\Modules\Pos\Actions\RecalculateOrderAction;
use App\Modules\Pos\Actions\VerifySupervisorPinAction;
use App\Modules\Pos\Enums\DiscountType;
use App\Modules\Pos\Enums\OrderType;
use App\Modules\Pos\Enums\PaymentType;
use App\Modules\Pos\Services\CashShiftService;
use App\Modules\Pos\Support\Money;
use App\Support\Access\RoleDefinitions;
use App\Support\Audit\ActivityLogger;
use App\Support\Tenant\OutletManager;
use App\Support\Tenant\TenantManager;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.base')]
class Cashier extends Component
{
    public ?int $orderId = null;

    public string $search = '';

    public ?int $categoryId = null;

    public string $orderType = '';

    /** Nama pelanggan — wajib diisi sebelum item pertama bisa masuk keranjang. */
    public string $customerName = '';

    /** Nomor meja — wajib hanya untuk tipe Dine In. */
    public string $tableNumber = '';

    /** Catatan bebas untuk dapur (opsional). */
    public string $orderNotes = '';

    /** Drawer keranjang untuk tablet/HP. */
    public bool $showCart = false;

    /** Toast ringan karena layar POS memakai layout kiosk sendiri. */
    public ?string $toastMessage = null;

    public string $toastType = 'success';

    public bool $showProductModal = false;

    public ?int $modalProductId = null;

    public ?int $modalVariantId = null;

    /** @var array<int, int|array<int, int>> */
    public array $modalModifiers = [];

    public bool $showDiscountModal = false;

    public ?string $discountType = null;

    public float $discountValue = 0;

    public string $discountPin = '';

    public bool $showPaymentModal = false;

    public ?int $paymentMethodId = null;

    public float $amountPaid = 0;

    public ?string $reference = null;

    /** @var array<int, array{method_id: int|null, amount: float, reference: string|null}> */
    public array $extraPayments = [];

    public bool $showShiftModal = false;

    public float $openingFloat = 0;

    public bool $showCloseShiftModal = false;

    public float $countedCash = 0;

    public ?string $shiftNotes = null;

    #[Computed]
    public function outlet(): ?Outlet
    {
        return app(OutletManager::class)->current();
    }

    /**
     * Role of the signed-in user inside the current tenant, for the header chip.
     */
    #[Computed]
    public function roleLabel(): string
    {
        $user = auth()->user();
        $tenant = app(TenantManager::class)->current();
        $role = $tenant !== null && $user instanceof User ? (string) $user->roleIn($tenant) : '';

        return RoleDefinitions::label($role);
    }

    #[Computed]
    public function shift(): ?CashShift
    {
        $outlet = $this->outlet;
        $user = auth()->user();

        if ($outlet === null || ! $user instanceof User) {
            return null;
        }

        return app(CashShiftService::class)->openFor($outlet, $user);
    }

    #[Computed]
    public function requireShift(): bool
    {
        return (bool) app(TenantManager::class)->current()?->setting('require_shift', false);
    }

    #[Computed]
    public function discountThreshold(): float
    {
        return (float) app(TenantManager::class)->current()?->setting('discount_pin_threshold', 0);
    }

    #[Computed]
    public function discountRequiresPin(): bool
    {
        $threshold = $this->discountThreshold;

        if ($threshold <= 0 || $this->order === null || $this->discountType === null) {
            return false;
        }

        $preview = $this->discountType === 'percent'
            ? (float) $this->order->subtotal * $this->discountValue / 100
            : $this->discountValue;

        return $preview >= $threshold;
    }

    #[Computed]
    public function order(): ?Order
    {
        if ($this->orderId === null) {
            return null;
        }

        return Order::with(['items.modifiers'])->find($this->orderId);
    }

    /**
     * Langkah alur kasir: 1 pilih menu, 2 lengkapi detail, 3 bayar, 4 selesai.
     */
    #[Computed]
    public function currentStep(): int
    {
        if ($this->order === null || $this->order->items->isEmpty()) {
            return 1;
        }

        if (! $this->detailsCompleted()) {
            return 2;
        }

        return 3;
    }

    #[Computed]
    public function stepHint(): string
    {
        return match ($this->currentStep) {
            1 => 'Sentuh menu di sebelah kiri untuk mulai mengisi keranjang.',
            2 => 'Lengkapi tipe pesanan dan nama pelanggan sebelum membayar.',
            default => 'Periksa keranjang, lalu tekan tombol Bayar.',
        };
    }

    public function detailsCompleted(): bool
    {
        if ($this->orderType === '' || trim($this->customerName) === '') {
            return false;
        }

        return $this->orderType !== 'dine_in' || trim($this->tableNumber) !== '';
    }

    /**
     * @return Collection<int, Category>
     */
    #[Computed]
    public function categories(): Collection
    {
        return Category::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
    }

    /**
     * @return Collection<int, Product>
     */
    #[Computed]
    public function products(): Collection
    {
        return Product::query()
            ->where('is_active', true)
            ->when($this->categoryId, fn ($query) => $query->where('category_id', $this->categoryId))
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
            ->with(['variants', 'category'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, PaymentMethod>
     */
    #[Computed]
    public function paymentMethods(): Collection
    {
        return PaymentMethod::query()->where('is_active', true)->orderBy('sort_order')->get();
    }

    /**
     * @return Collection<int, Bundle>
     */
    #[Computed]
    public function bundles(): Collection
    {
        return Bundle::query()
            ->where('is_active', true)
            ->with('items.product')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function modalProduct(): ?Product
    {
        if ($this->modalProductId === null) {
            return null;
        }

        return Product::with(['variants', 'modifierGroups.modifiers'])->find($this->modalProductId);
    }

    public function selectCategory(?int $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function toggleCart(): void
    {
        $this->showCart = ! $this->showCart;
    }

    public function openCart(): void
    {
        $this->showCart = true;
    }

    public function closeCart(): void
    {
        $this->showCart = false;
    }

    public function toast(string $message, string $type = 'success'): void
    {
        $this->toastMessage = $message;
        $this->toastType = $type;
    }

    public function clearToast(): void
    {
        $this->toastMessage = null;
    }

    /**
     * Category rail icon (SVG path), matched loosely from the category name
     * so common cafe menu groups get a recognizable glyph in the POS rail.
     */
    public function categoryIcon(string $name): string
    {
        $key = mb_strtolower($name);

        return match (true) {
            str_contains($key, 'pastry'), str_contains($key, 'roti'),
            str_contains($key, 'cake'), str_contains($key, 'kue'), str_contains($key, 'dessert') => 'M4 20.5h16M6 20.5v-5.5a3 3 0 013-3h6a3 3 0 013 3v5.5M12 11V9M10 9a2 2 0 114 0',
            str_contains($key, 'non-kopi'), str_contains($key, 'minuman'),
            str_contains($key, 'tea'), str_contains($key, 'teh'), str_contains($key, 'jus') => 'M3 9h14v5a5 5 0 01-5 5H8a5 5 0 01-5-5V9zM17 10h2a2.5 2.5 0 010 5h-2M5 6h9',
            str_contains($key, 'kopi'), str_contains($key, 'coffee') => 'M3 9h14v5a5 5 0 01-5 5H8a5 5 0 01-5-5V9zM17 10h2a2.5 2.5 0 010 5h-2M7 3.5c0 .9-.9 1.4-.9 2.5M11 3.5c0 .9-.9 1.4-.9 2.5',
            str_contains($key, 'makanan'), str_contains($key, 'food'), str_contains($key, 'rice') => 'M6 3.5v17M4 3.5v4a2 2 0 004 0v-4M16 20.5v-17M16 10c2.2-1.5 3.5-3.5 3.5-6.5h-3.5',
            default => 'M9 4a1 1 0 011-1h4a1 1 0 011 1v2h3.5a1 1 0 011 1v13h-15V7a1 1 0 011-1H9V4zM3 9h18',
        };
    }

    public function chooseProduct(int $productId): void
    {
        $product = Product::with(['variants', 'modifierGroups'])->findOrFail($productId);

        if (! $product->isOrderable()) {
            return;
        }

        if ($product->variants->isNotEmpty() || $product->modifierGroups->isNotEmpty()) {
            $this->openProductModal($product);

            return;
        }

        $this->addLine($product, null, []);
    }

    public function addBundle(int $bundleId): void
    {
        $bundle = $this->bundles->firstWhere('id', $bundleId);

        if ($bundle === null) {
            return;
        }

        $order = $this->ensureOrder();

        app(AddOrderItemAction::class)->execute($order, [
            'bundle_id' => $bundle->getKey(),
            'name' => $bundle->name,
            'unit_price' => (float) $bundle->price,
            'quantity' => 1,
            'cogs' => $bundle->totalCost(),
        ]);

        $this->recalculate();
        $this->showCart = true;
        $this->toast($bundle->name.' ditambahkan ke keranjang.');
    }

    public function confirmProduct(): void
    {
        $product = $this->modalProduct;

        if ($product === null) {
            return;
        }

        $modifiers = $this->resolveSelectedModifiers($product);

        $variant = $this->modalVariantId !== null
            ? $product->variants->firstWhere('id', $this->modalVariantId)
            : ($product->variants->firstWhere('is_default', true) ?? $product->variants->first());

        if ($product->variants->isNotEmpty() && $variant === null) {
            throw ValidationException::withMessages(['modalVariantId' => 'Pilih varian terlebih dahulu.']);
        }

        $this->addLine($product, $variant, $modifiers);

        $this->closeProductModal();
    }

    public function incrementItem(int $itemId): void
    {
        $item = $this->order?->items->firstWhere('id', $itemId);

        if ($item === null) {
            return;
        }

        $this->updateItemQuantity($item, (float) $item->quantity + 1);
    }

    public function decrementItem(int $itemId): void
    {
        $item = $this->order?->items->firstWhere('id', $itemId);

        if ($item === null) {
            return;
        }

        $quantity = (float) $item->quantity - 1;

        if ($quantity <= 0) {
            $item->delete();
        } else {
            $this->updateItemQuantity($item, $quantity);
        }

        $this->recalculate();

        if ($this->order?->items->isEmpty()) {
            $this->orderId = null;
        }
    }

    public function removeItem(int $itemId): void
    {
        $item = $this->order?->items->firstWhere('id', $itemId);

        if ($item === null) {
            return;
        }

        $item->delete();
        $this->recalculate();

        if ($this->order?->items->isEmpty()) {
            $this->orderId = null;
        }

        $this->toast('Item dihapus dari keranjang.');
    }

    public function updatedOrderType(): void
    {
        $this->saveOrderDetails();
    }

    public function updatedCustomerName(): void
    {
        $this->saveOrderDetails();
    }

    public function updatedTableNumber(): void
    {
        $this->saveOrderDetails();
    }

    public function updatedOrderNotes(): void
    {
        $this->saveOrderDetails();
    }

    /**
     * Aturan detail pesanan: tipe & nama pelanggan wajib, nomor meja
     * wajib khusus Dine In. Dipanggil saat order mulai dibuat.
     *
     * @return array<string, array<int, string>|string>
     */
    public static function detailRules(): array
    {
        return [
            'orderType' => ['required', 'in:dine_in,takeaway,pickup'],
            'customerName' => ['required', 'string', 'max:100'],
            'tableNumber' => ['required_if:orderType,dine_in', 'nullable', 'string', 'max:30'],
            'orderNotes' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function saveOrderDetails(): void
    {
        $this->order?->fill([
            'type' => $this->orderType !== '' ? OrderType::from($this->orderType) : null,
            'customer_name' => $this->customerName !== '' ? $this->customerName : null,
            'table_number' => $this->orderType === 'dine_in' && $this->tableNumber !== ''
                ? $this->tableNumber
                : null,
            'notes' => $this->orderNotes !== '' ? $this->orderNotes : null,
        ])->save();
    }

    public function openDiscountModal(): void
    {
        if ($this->order === null) {
            return;
        }

        $this->discountType = $this->order->discount_type?->value;
        $this->discountValue = (float) $this->order->discount_value;
        $this->discountPin = '';
        $this->showDiscountModal = true;
    }

    public function applyDiscount(ApplyDiscountAction $action): void
    {
        if ($this->order === null) {
            return;
        }

        $this->validate([
            'discountType' => ['nullable', 'in:percent,fixed'],
            'discountValue' => ['numeric', 'min:0'],
        ]);

        if ($this->discountRequiresPin) {
            app(VerifySupervisorPinAction::class)->execute($this->discountPin);

            app(ActivityLogger::class)->log(
                'discount.approved',
                'Diskon disetujui supervisor untuk '.$this->order->number,
                $this->order,
                ['type' => $this->discountType, 'value' => (float) $this->discountValue],
            );
        }

        $action->execute(
            $this->order,
            $this->discountType !== null ? DiscountType::from($this->discountType) : null,
            (float) $this->discountValue,
        );

        $this->showDiscountModal = false;
        $this->discountPin = '';
        $this->toast('Diskon berhasil diterapkan.');
    }

    public function openPaymentModal(): void
    {
        if ($this->order === null || $this->order->items->isEmpty()) {
            $this->toast('Keranjang masih kosong. Pilih menu terlebih dahulu.', 'error');

            return;
        }

        try {
            $this->validate(self::detailRules(), [], [
                'orderType' => 'tipe pesanan',
                'customerName' => 'nama pelanggan',
                'tableNumber' => 'nomor meja',
            ]);
        } catch (ValidationException $exception) {
            $this->toast('Lengkapi tipe pesanan dan nama pelanggan sebelum membayar.', 'error');
            $this->showCart = true;

            throw $exception;
        }

        $this->paymentMethodId = $this->paymentMethods->first()?->getKey();
        $this->amountPaid = (float) $this->order->grand_total;
        $this->reference = null;
        $this->extraPayments = [];
        $this->showPaymentModal = true;
        $this->showCart = false;
        $this->resetErrorBag();
    }

    public function addPaymentLine(): void
    {
        $this->extraPayments[] = [
            'method_id' => $this->paymentMethods->first()?->getKey(),
            'amount' => 0.0,
            'reference' => null,
        ];
    }

    public function removePaymentLine(int $index): void
    {
        if (! array_key_exists($index, $this->extraPayments)) {
            return;
        }

        unset($this->extraPayments[$index]);
        $this->extraPayments = array_values($this->extraPayments);
    }

    #[Computed]
    public function totalPaidPreview(): float
    {
        $extra = array_reduce(
            $this->extraPayments,
            fn (float $carry, array $line): float => $carry + (float) ($line['amount'] ?? 0),
            0.0,
        );

        return Money::round((float) $this->amountPaid + $extra);
    }

    public function pay(PayOrderAction $action): void
    {
        $order = $this->order;

        if ($order === null) {
            return;
        }

        if ($this->requireShift && $this->shift === null) {
            throw ValidationException::withMessages([
                'paymentMethodId' => 'Buka shift terlebih dahulu sebelum menerima pembayaran.',
            ]);
        }

        $this->validate([
            'paymentMethodId' => ['required', 'integer'],
            'amountPaid' => ['required', 'numeric', 'min:0'],
            'reference' => ['nullable', 'string', 'max:100'],
            'extraPayments.*.method_id' => ['nullable', 'integer'],
            'extraPayments.*.amount' => ['required', 'numeric'],
        ]);

        $lines = [$this->paymentLine((int) $this->paymentMethodId, (float) $this->amountPaid, $this->reference)];

        foreach ($this->extraPayments as $index => $extra) {
            $methodId = $extra['method_id'] ?? null;

            if ($methodId === null) {
                continue;
            }

            $lines[] = $this->paymentLine((int) $methodId, (float) $extra['amount'], $extra['reference'] ?? null, $index);
        }

        $action->execute($order, $lines);

        $this->showPaymentModal = false;
        $this->redirect(route('receipt', ['order' => $order->getKey()]), navigate: true);
    }

    /**
     * @return array{payment_method_id: int, method_name: string, method_type: PaymentType, amount: float, reference: string|null}
     */
    private function paymentLine(int $methodId, float $amount, ?string $reference, ?int $extraIndex = null): array
    {
        $method = $this->paymentMethods->firstWhere('id', $methodId);

        if ($method === null) {
            throw ValidationException::withMessages([
                $extraIndex === null ? 'paymentMethodId' : "extraPayments.{$extraIndex}.method_id" => 'Metode pembayaran tidak valid.',
            ]);
        }

        return [
            'payment_method_id' => $method->getKey(),
            'method_name' => $method->name,
            'method_type' => $method->type,
            'amount' => Money::round($amount),
            'reference' => filled($reference) ? $reference : $this->paymentReference($method, $extraIndex),
        ];
    }

    private function paymentReference(PaymentMethod $method, ?int $extraIndex): string
    {
        $orderNumber = $this->order?->number ?? 'PENDING';
        $lineNumber = ($extraIndex ?? -1) + 2;

        return strtoupper($method->code).'-'.$orderNumber.'-'.$lineNumber;
    }

    public function newOrder(): void
    {
        $this->orderId = null;
        $this->reset(['orderType', 'customerName', 'tableNumber', 'orderNotes', 'showCart']);
        $this->resetErrorBag();
        $this->toast('Pesanan baru siap. Silakan pilih menu.');
    }

    public function openShift(): void
    {
        $outlet = $this->outlet;
        $user = auth()->user();

        if ($outlet === null || ! $user instanceof User) {
            return;
        }

        $this->validate(['openingFloat' => ['numeric', 'min:0']]);

        app(CashShiftService::class)->open($outlet, $user, (float) $this->openingFloat);

        $this->showShiftModal = false;
        $this->openingFloat = 0;
        unset($this->shift);
        $this->toast('Shift kasir berhasil dibuka.');
    }

    public function closeShift(): void
    {
        $shift = $this->shift;
        $user = auth()->user();

        if ($shift === null || ! $user instanceof User) {
            return;
        }

        $this->validate(['countedCash' => ['numeric', 'min:0']]);

        app(CashShiftService::class)->close($shift, $user, (float) $this->countedCash, $this->shiftNotes);

        $this->showCloseShiftModal = false;
        $this->reset('countedCash', 'shiftNotes');
        unset($this->shift);
        $this->toast('Shift kasir ditutup dan rekap tersimpan.');
    }

    private function openProductModal(Product $product): void
    {
        $this->modalProductId = $product->getKey();
        $this->resetErrorBag();
        $this->modalVariantId = $product->variants->firstWhere('is_default', true)?->getKey()
            ?? $product->variants->first()?->getKey();
        $this->modalModifiers = [];

        foreach ($product->modifierGroups as $group) {
            $this->modalModifiers[$group->getKey()] = $group->max_select === 1 ? null : [];
        }

        $this->showProductModal = true;
    }

    private function closeProductModal(): void
    {
        $this->showProductModal = false;
        $this->modalProductId = null;
        $this->modalVariantId = null;
        $this->modalModifiers = [];
    }

    /**
     * @param  array<int, array{modifier_id: int, name: string, price: float}>  $modifiers
     */
    private function addLine(Product $product, ?ProductVariant $variant, array $modifiers): void
    {
        $order = $this->ensureOrder();

        $basePrice = $variant !== null ? (float) $variant->price : (float) $product->price;

        app(AddOrderItemAction::class)->execute($order, [
            'product_id' => $product->getKey(),
            'product_variant_id' => $variant?->getKey(),
            'name' => $product->name,
            'variant_name' => $variant?->name,
            'unit_price' => $basePrice,
            'quantity' => 1,
            'cogs' => (float) $product->cost,
            'modifiers' => $modifiers,
        ]);

        $this->recalculate();
        $this->showCart = true;
        $this->toast($product->name.' ditambahkan ke keranjang.');
    }

    private function ensureOrder(): Order
    {
        if ($this->order !== null) {
            return $this->order;
        }

        $outlet = $this->outlet;

        if ($outlet === null) {
            throw ValidationException::withMessages(['search' => 'Outlet belum dikonfigurasi.']);
        }

        // Keranjang boleh diisi lebih dulu. Tipe & nama pelanggan baru wajib
        // saat kasir membuka pembayaran.
        $order = app(CreateOrderAction::class)->execute(
            $outlet,
            [
                'type' => $this->orderType !== '' ? OrderType::from($this->orderType) : null,
                'customer_name' => $this->customerName !== '' ? $this->customerName : null,
                'notes' => $this->orderNotes !== '' ? $this->orderNotes : null,
                'table_number' => $this->orderType === 'dine_in' && $this->tableNumber !== ''
                    ? $this->tableNumber
                    : null,
            ],
            auth()->user(),
        );

        if ($this->shift !== null) {
            $order->fill(['cash_shift_id' => $this->shift->getKey()])->save();
        }

        $this->orderId = $order->getKey();
        $this->recalculate($order);

        return $order;
    }

    private function updateItemQuantity(OrderItem $item, float $quantity): void
    {
        $item->quantity = $quantity;
        $item->line_total = Money::round(((float) $item->unit_price * $quantity) - (float) $item->discount_amount);
        $item->save();
    }

    private function recalculate(?Order $order = null): void
    {
        $order ??= $this->order;

        if ($order !== null) {
            app(RecalculateOrderAction::class)->execute($order);
        }
    }

    /**
     * @return array<int, array{modifier_id: int, name: string, price: float}>
     */
    private function resolveSelectedModifiers(Product $product): array
    {
        $selected = [];

        foreach ($product->modifierGroups as $group) {
            /** @var ModifierGroup $group */
            $ids = array_values(array_filter(Arr::wrap($this->modalModifiers[$group->getKey()] ?? [])));

            if ($group->is_required && count($ids) < max(1, $group->min_select)) {
                throw ValidationException::withMessages([
                    'modalModifiers' => "Grup \"{$group->name}\" wajib dipilih.",
                ]);
            }

            if ($group->max_select > 0 && count($ids) > $group->max_select) {
                throw ValidationException::withMessages([
                    'modalModifiers' => "Grup \"{$group->name}\" maksimal {$group->max_select} pilihan.",
                ]);
            }

            foreach (array_slice($ids, 0, $group->max_select) as $id) {
                $modifier = $group->modifiers->firstWhere('id', (int) $id);

                if ($modifier !== null) {
                    $selected[] = [
                        'modifier_id' => $modifier->getKey(),
                        'name' => $modifier->name,
                        'price' => (float) $modifier->price,
                    ];
                }
            }
        }

        return $selected;
    }

    public function render(): View
    {
        return view('livewire.pos.cashier');
    }
}
