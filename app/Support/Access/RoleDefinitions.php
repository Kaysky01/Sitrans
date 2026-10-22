<?php

namespace App\Support\Access;

use App\Models\ActivityLog;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\ModifierGroup;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\User;

/**
 * Single source of truth for roles and their permissions. Roles are created
 * per tenant (spatie teams, keyed by tenant_id); permissions are global.
 */
final class RoleDefinitions
{
    public const OWNER = 'owner';

    public const MANAGER = 'manager';

    public const CASHIER = 'cashier';

    public const KITCHEN = 'kitchen';

    public const SUPER_ADMIN = 'super_admin';

    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return [
            'pos.use',
            'orders.view',
            'orders.void',
            'orders.refund',
            'menu.view',
            'menu.manage',
            'inventory.manage',
            'finance.manage',
            'reports.view',
            'activity.view',
            'settings.manage',
            'users.manage',
            'outlets.manage',
        ];
    }

    /**
     * Maps each tenant-owned model to the permissions its Filament resource
     * requires. A `view` permission controls browsing the resource, `manage`
     * controls creating, editing and deleting. A `null` entry means the action
     * is never allowed (e.g. orders are read-only in the back office).
     *
     * @return array<class-string, array{view: ?string, manage: ?string}>
     */
    public static function modelPermissions(): array
    {
        return [
            Product::class => ['view' => 'menu.view', 'manage' => 'menu.manage'],
            Category::class => ['view' => 'menu.view', 'manage' => 'menu.manage'],
            ModifierGroup::class => ['view' => 'menu.view', 'manage' => 'menu.manage'],
            Bundle::class => ['view' => 'menu.view', 'manage' => 'menu.manage'],
            StockItem::class => ['view' => 'inventory.manage', 'manage' => 'inventory.manage'],
            Order::class => ['view' => 'orders.view', 'manage' => null],
            Outlet::class => ['view' => 'outlets.manage', 'manage' => 'outlets.manage'],
            PaymentMethod::class => ['view' => 'finance.manage', 'manage' => 'finance.manage'],
            User::class => ['view' => 'users.manage', 'manage' => 'users.manage'],
            ActivityLog::class => ['view' => 'activity.view', 'manage' => null],
        ];
    }

    /**
     * Role name => permission names. Owner implicitly receives every permission.
     *
     * @return array<string, array<int, string>>
     */
    public static function map(): array
    {
        return [
            self::OWNER => self::permissions(),
            self::MANAGER => [
                'pos.use', 'orders.view', 'orders.void', 'orders.refund',
                'menu.view', 'menu.manage', 'inventory.manage', 'reports.view',
                'activity.view', 'settings.manage', 'outlets.manage',
            ],
            self::CASHIER => ['pos.use', 'orders.view', 'menu.view'],
            self::KITCHEN => ['orders.view'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::OWNER => 'Pemilik',
            self::MANAGER => 'Manajer',
            self::CASHIER => 'Kasir',
            self::KITCHEN => 'Dapur',
        ];
    }

    public static function label(string $role): string
    {
        return self::labels()[$role] ?? 'Staf';
    }
}
