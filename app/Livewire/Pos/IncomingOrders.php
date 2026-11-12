<?php

namespace App\Livewire\Pos;

use App\Models\Order;
use App\Models\Outlet;
use App\Modules\Pos\Actions\AdvanceOrderFulfillmentAction;
use App\Modules\Pos\Enums\OrderFulfillmentStatus;
use App\Support\Tenant\OutletManager;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Papan kerja pesanan masuk. Menampilkan antrean aktif per outlet dan
 * menyediakan aksi Terima -> Proses -> Selesai.
 */
class IncomingOrders extends Component
{
    public string $filter = 'all';

    public ?string $toastMessage = null;

    public string $toastType = 'success';

    #[Computed]
    public function outlet(): ?Outlet
    {
        return app(OutletManager::class)->current();
    }

    /**
     * Seluruh antrean aktif, dipakai untuk menghitung posisi antrean.
     *
     * @return Collection<int, Order>
     */
    #[Computed]
    public function allActiveOrders(): Collection
    {
        return Order::query()
            ->with(['items', 'outlet', 'cashier'])
            ->activeFulfillment()
            ->when($this->outlet !== null, fn ($query) => $query->where('outlet_id', $this->outlet->getKey()))
            ->orderBy('ordered_at')
            ->orderBy('id')
            ->get();
    }

    /**
     * @return array<int, int> order id => nomor antrean
     */
    #[Computed]
    public function queuePositions(): array
    {
        $positions = [];

        foreach ($this->allActiveOrders as $index => $order) {
            $positions[$order->getKey()] = $index + 1;
        }

        return $positions;
    }

    /**
     * @return Collection<int, Order>
     */
    #[Computed]
    public function orders(): Collection
    {
        if ($this->filter === 'all') {
            return $this->allActiveOrders;
        }

        $status = OrderFulfillmentStatus::tryFrom($this->filter);

        if ($status === null) {
            return $this->allActiveOrders;
        }

        return $this->allActiveOrders
            ->filter(fn (Order $order): bool => $order->fulfillment_status === $status)
            ->values();
    }

    public function totalActive(): int
    {
        return $this->allActiveOrders->count();
    }

    public function countByStatus(OrderFulfillmentStatus $status): int
    {
        return $this->allActiveOrders
            ->filter(fn (Order $order): bool => $order->fulfillment_status === $status)
            ->count();
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    public function accept(int $orderId): void
    {
        $this->advance($orderId, OrderFulfillmentStatus::Accepted);
    }

    public function process(int $orderId): void
    {
        $this->advance($orderId, OrderFulfillmentStatus::Processing);
    }

    public function complete(int $orderId): void
    {
        $this->advance($orderId, OrderFulfillmentStatus::Completed);
    }

    public function clearToast(): void
    {
        $this->toastMessage = null;
    }

    private function advance(int $orderId, OrderFulfillmentStatus $target): void
    {
        $order = $this->allActiveOrders->firstWhere('id', $orderId);

        if ($order === null) {
            $this->toast('Pesanan tidak ditemukan atau sudah selesai.', 'error');

            return;
        }

        try {
            app(AdvanceOrderFulfillmentAction::class)->execute($order, $target, auth()->user());
        } catch (ValidationException $exception) {
            $this->toast(
                collect($exception->errors())->flatten()->first() ?? 'Status pesanan tidak dapat diubah.',
                'error',
            );

            return;
        }

        unset($this->allActiveOrders, $this->orders, $this->queuePositions);

        $this->toast("Pesanan {$order->number} → {$target->label()}.");
    }

    public function toast(string $message, string $type = 'success'): void
    {
        $this->toastMessage = $message;
        $this->toastType = $type;
    }

    public function render(): View
    {
        return view('livewire.pos.incoming-orders');
    }
}
