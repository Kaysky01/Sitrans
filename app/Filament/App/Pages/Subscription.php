<?php

namespace App\Filament\App\Pages;

use App\Models\Tenant;
use App\Support\Tenant\TenantManager;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;

class Subscription extends Page
{
    protected string $view = 'filament.app.pages.subscription';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Langganan';

    protected static ?string $title = 'Langganan';

    protected static ?int $navigationSort = 5;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('settings.manage') ?? false;
    }

    #[Computed]
    public function tenant(): ?Tenant
    {
        return app(TenantManager::class)->current();
    }

    public function daysLeft(): ?int
    {
        $tenant = $this->tenant;

        if ($tenant === null || $tenant->trial_ends_at === null) {
            return null;
        }

        return max(0, (int) now()->diffInDays($tenant->trial_ends_at, false));
    }

    public function statusLabel(): string
    {
        $tenant = $this->tenant;

        if ($tenant === null) {
            return 'Tidak diketahui';
        }

        return match ($tenant->status) {
            Tenant::STATUS_TRIAL => $tenant->onTrial() ? 'Masa uji coba' : 'Uji coba berakhir',
            Tenant::STATUS_ACTIVE => 'Aktif',
            Tenant::STATUS_PAST_DUE => 'Menunggu pembayaran',
            Tenant::STATUS_SUSPENDED => 'Ditangguhkan',
            default => $tenant->status,
        };
    }

    public function statusColor(): string
    {
        return match ($this->tenant?->status) {
            Tenant::STATUS_ACTIVE => 'success',
            Tenant::STATUS_TRIAL => 'warning',
            default => 'danger',
        };
    }

    public function regenerateApiKey(): void
    {
        $tenant = $this->tenant;

        if ($tenant === null) {
            return;
        }

        $tenant->update(['api_key' => 'sk_'.Str::random(48)]);

        Notification::make()
            ->title('Kunci API diperbarui')
            ->success()
            ->send();
    }
}
