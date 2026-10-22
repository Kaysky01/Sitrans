<?php

namespace App\Filament\App\Widgets;

use App\Support\Tenant\TenantManager;
use Filament\Widgets\Widget;

class TrialBanner extends Widget
{
    protected string $view = 'filament.app.widgets.trial-banner';

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return app(TenantManager::class)->current()?->onTrial() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $tenant = app(TenantManager::class)->current();
        $daysLeft = $tenant?->trial_ends_at !== null
            ? max(0, (int) now()->diffInDays($tenant->trial_ends_at, false))
            : 0;

        return [
            'daysLeft' => $daysLeft,
            'endsAt' => $tenant?->trial_ends_at,
        ];
    }
}
