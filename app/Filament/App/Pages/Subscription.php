<?php

namespace App\Filament\App\Pages;

use App\Models\Tenant;
use App\Support\Access\RoleDefinitions;
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

    protected static ?string $title = 'Langganan & Paket Kafe';

    protected static ?int $navigationSort = 5;

    public static function getNavigationBadge(): ?string
    {
        return 'Owner';
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        $tenant = app(TenantManager::class)->current();

        if ($user === null) {
            return false;
        }

        // Pengguna tetap bisa melihat status saat langganan berakhir karena
        // middleware mengarahkan mereka ke halaman ini.
        if ($tenant !== null && ! $tenant->isActive()) {
            return true;
        }

        return $tenant !== null && $user->roleIn($tenant) === RoleDefinitions::OWNER;
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
