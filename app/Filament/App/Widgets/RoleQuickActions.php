<?php

namespace App\Filament\App\Widgets;

use App\Models\Order;
use App\Models\StockItem;
use App\Models\User;
use App\Modules\Pos\Enums\OrderStatus;
use App\Support\Access\RoleDefinitions;
use App\Support\Tenant\TenantManager;
use Filament\Widgets\Widget;

/**
 * Operational dashboard lead card. Its tasks and metrics follow the signed-in
 * user's role, so the dashboard gives each person a useful next action.
 */
class RoleQuickActions extends Widget
{
    protected string $view = 'filament.app.widgets.role-quick-actions';

    protected static ?int $sort = -1;

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->can('reports.view') || $user->can('pos.use') || $user->can('orders.view');
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $user = auth()->user();
        $tenant = app(TenantManager::class)->current();
        $role = $tenant !== null && $user instanceof User
            ? ($user->roleIn($tenant) ?? RoleDefinitions::OWNER)
            : RoleDefinitions::OWNER;

        $canViewOrders = $user?->can('orders.view') ?? false;
        $canManageInventory = $user?->can('inventory.manage') ?? false;
        $today = now()->startOfDay();

        $openOrders = $canViewOrders
            ? Order::query()->where('status', OrderStatus::Open)->count()
            : 0;
        $todayOrders = $canViewOrders
            ? Order::query()->where('created_at', '>=', $today)->count()
            : 0;
        $lowStock = $canManageInventory
            ? StockItem::query()
                ->whereColumn('quantity', '<=', 'reorder_level')
                ->count()
            : 0;

        /** @var User|null $user */
        return [
            'tenant' => $tenant,
            'user' => $user,
            'role' => $role,
            'roleLabel' => RoleDefinitions::label($role),
            'canUsePos' => $user?->can('pos.use') ?? false,
            'canViewOrders' => $canViewOrders,
            'canViewReports' => $user?->can('reports.view') ?? false,
            'canManageSettings' => $user?->can('settings.manage') ?? false,
            'canManageInventory' => $canManageInventory,
            'openOrders' => $openOrders,
            'todayOrders' => $todayOrders,
            'lowStock' => $lowStock,
        ];
    }
}
