<?php

namespace App\Livewire\Public;

use App\Exceptions\AppError;
use App\Models\Category;
use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Tenant;
use App\Modules\Pos\Actions\CreateOnlineOrderAction;
use App\Modules\Pos\Enums\OrderType;
use App\Support\Tenant\TenantManager;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Storefront extends Component
{
    public Tenant $tenant;

    public string $search = '';

    public ?int $categoryId = null;

    public string $customerName = '';

    /** Nomor meja — wajib hanya saat tipe Dine In. */
    public string $tableNumber = '';

    public string $notes = '';

    public string $type = '';

    public string $paymentMethod = 'qris';

    public ?string $placedPaymentMethod = null;

    /**
     * @var array<int, array{product_id: int, product_variant_id: int|null, name: string, variant_name: string|null, unit_price: float, quantity: float}>
     */
    public array $cart = [];

    /**
     * @var array<int, int>
     */
    public array $selectedVariants = [];

    public ?string $placedNumber = null;

    public float $placedTotal = 0;

    public function mount(Tenant $tenant): void
    {
        abort_unless($tenant->isActive(), 404);

        $this->tenant = $tenant;

        app(TenantManager::class)->set($tenant);

        $firstMethod = PaymentMethod::query()->where('is_active', true)->orderBy('sort_order')->first();
        if ($firstMethod !== null) {
            $this->paymentMethod = $firstMethod->code;
        }
    }

    /**
     * @return Collection<int, Category>
     */
    #[Computed]
    public function categories(): Collection
    {
        return Category::query()
            ->where('is_active', true)
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
        return PaymentMethod::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * @return Collection<int, Product>
     */
    #[Computed]
    public function products(): Collection
    {
        return Product::query()
            ->where('is_active', true)
            ->where('is_sold_out', false)
            ->when($this->categoryId !== null, fn ($query) => $query->where('category_id', $this->categoryId))
            ->when(trim($this->search) !== '', fn ($query) => $query->where('name', 'like', '%'.trim($this->search).'%'))
            ->with(['variants' => fn ($query) => $query->orderBy('id')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function cartTotal(): float
    {
        return array_reduce(
            $this->cart,
            fn (float $carry, array $line): float => $carry + $line['unit_price'] * $line['quantity'],
            0.0,
        );
    }

    #[Computed]
    public function cartCount(): int
    {
        return (int) array_sum(array_column($this->cart, 'quantity'));
    }

    public function addToCart(int $productId): void
    {
        $product = Product::query()->with('variants')->find($productId);

        if ($product === null) {
            $this->addError('cart', 'Menu tidak ditemukan. Muat ulang halaman untuk daftar menu terbaru.');

            return;
        }

        if (! $product->isOrderable()) {
            $this->addError('cart', $product->name.' sedang tidak tersedia. Hapus dari keranjang atau pilih menu lain.');

            return;
        }

        $variant = null;

        if ($product->variants->isNotEmpty()) {
            $variantId = (int) ($this->selectedVariants[$productId] ?? $product->variants->first()->getKey());
            $variant = $product->variants->firstWhere('id', $variantId);

            if ($variant === null) {
                return;
            }
        }

        foreach ($this->cart as $index => $line) {
            if ($line['product_id'] === $product->getKey() && $line['product_variant_id'] === $variant?->getKey()) {
                $this->cart[$index]['quantity'] += 1;

                return;
            }
        }

        $this->cart[] = [
            'product_id' => $product->getKey(),
            'product_variant_id' => $variant?->getKey(),
            'name' => $product->name,
            'variant_name' => $variant?->name,
            'unit_price' => (float) ($variant !== null ? $variant->price : $product->price),
            'quantity' => 1.0,
        ];
    }

    public function increment(int $index): void
    {
        if (isset($this->cart[$index])) {
            $this->cart[$index]['quantity'] += 1;
        }
    }

    public function decrement(int $index): void
    {
        if (! isset($this->cart[$index])) {
            return;
        }

        $this->cart[$index]['quantity'] -= 1;

        if ($this->cart[$index]['quantity'] < 1) {
            $this->removeLine($index);
        }
    }

    public function removeLine(int $index): void
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    public function placeOrder(CreateOnlineOrderAction $action): void
    {
        if ($this->cart === []) {
            $this->addError('cart', 'Keranjang masih kosong.');

            return;
        }

        $this->validate([
            // Sama dengan kasir: tipe & nama pelanggan wajib, meja wajib untuk Dine In.
            'customerName' => ['required', 'string', 'max:100'],
            'tableNumber' => ['required_if:type,dine_in', 'nullable', 'string', 'max:30'],
            'type' => ['required', 'in:dine_in,takeaway,pickup'],
            'paymentMethod' => ['required', 'string'],
        ], [], [
            'customerName' => 'nama pelanggan',
            'tableNumber' => 'nomor meja',
            'type' => 'tipe pesanan',
            'paymentMethod' => 'metode pembayaran',
        ]);

        $outlet = Outlet::query()->where('is_active', true)->orderBy('id')->first()
            ?? Outlet::query()->orderBy('id')->first();

        if ($outlet === null) {
            $this->addError('cart', 'Outlet belum tersedia untuk menerima pesanan.');

            return;
        }

        try {
            $order = $action->execute($outlet, [
                'type' => OrderType::from($this->type),
                'customer_name' => $this->customerName,
                'table_number' => $this->type === 'dine_in' && $this->tableNumber !== ''
                    ? $this->tableNumber
                    : null,
                'notes' => $this->notes !== '' ? $this->notes : null,
                'payment_method' => $this->paymentMethod,
                'items' => array_map(fn (array $line): array => [
                    'product_id' => $line['product_id'],
                    'product_variant_id' => $line['product_variant_id'],
                    'quantity' => $line['quantity'],
                ], $this->cart),
            ]);
        } catch (ValidationException $exception) {
            $this->addError('cart', collect($exception->errors())->flatten()->first() ?? 'Pesanan gagal dibuat.');

            return;
        } catch (AppError $exception) {
            $this->addError('cart', $exception->getMessage());

            return;
        }

        $this->placedNumber = $order->number;
        $this->placedTotal = (float) $order->grand_total;
        $this->placedPaymentMethod = $this->paymentMethod;
        $this->cart = [];
        $this->customerName = '';
        $this->tableNumber = '';
        $this->type = '';
        $this->notes = '';
    }

    public function startOver(): void
    {
        $this->placedNumber = null;
        $this->placedTotal = 0;
        $this->placedPaymentMethod = null;
    }

    public function render(): View
    {
        return view('livewire.public.storefront', [
            'outlet' => $this->tenant->outlets()->where('is_active', true)->orderBy('id')->first(),
        ])->title($this->tenant->name);
    }
}
