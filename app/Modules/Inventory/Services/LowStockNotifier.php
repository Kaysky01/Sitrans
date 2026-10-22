<?php

namespace App\Modules\Inventory\Services;

use App\Models\StockItem;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

/**
 * Alerts the people who can act on stock (tenant owners and managers) when a
 * tracked item drops to or below its reorder level. Delivered as a Filament
 * database notification so it lands in the top bar bell.
 */
final class LowStockNotifier
{
    public function notify(StockItem $item): void
    {
        $item->loadMissing('product', 'variant');

        if ($item->tenant_id === null) {
            return;
        }

        $recipients = $this->recipients($item->tenant_id);

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::make()
            ->title($item->isOut() ? 'Stok habis' : 'Stok menipis')
            ->body($item->label().' tersisa '.$this->quantity($item->quantity).'.')
            ->warning()
            ->sendToDatabase($recipients);
    }

    /**
     * @return Collection<int, User>
     */
    private function recipients(int $tenantId): Collection
    {
        return User::query()
            ->whereHas('tenants', fn ($query) => $query
                ->whereKey($tenantId)
                ->where('tenant_user.is_active', true)
                ->whereIn('tenant_user.role', ['owner', 'manager']))
            ->get();
    }

    private function quantity(float $quantity): string
    {
        return rtrim(rtrim(number_format($quantity, 3, '.', ''), '0'), '.') ?: '0';
    }
}
