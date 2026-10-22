<?php

namespace App\Support\Access;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Authorizes a Filament resource against the permission map declared in
 * {@see RoleDefinitions}. Applied to every back office resource so that
 * navigation, pages, buttons and bulk actions all respect the signed-in
 * user's role (owner, manager, cashier, kitchen).
 */
trait ChecksResourcePermissions
{
    public static function canViewAny(): bool
    {
        return static::allowsResourcePermission('view');
    }

    public static function canView(Model $record): bool
    {
        return static::allowsResourcePermission('view');
    }

    public static function canCreate(): bool
    {
        return static::allowsResourcePermission('manage');
    }

    public static function canEdit(Model $record): bool
    {
        return static::allowsResourcePermission('manage');
    }

    public static function canDelete(Model $record): bool
    {
        return static::allowsResourcePermission('manage');
    }

    public static function canDeleteAny(): bool
    {
        return static::allowsResourcePermission('manage');
    }

    /**
     * A resource whose model is not mapped is left open (opt-in security);
     * a mapped model with a `null` permission denies the action outright.
     */
    protected static function allowsResourcePermission(string $ability): bool
    {
        $mapping = RoleDefinitions::modelPermissions()[static::getModel()] ?? null;

        if ($mapping === null) {
            return true;
        }

        $permission = $mapping[$ability] ?? null;

        if ($permission === null) {
            return false;
        }

        $user = auth()->user();

        return $user instanceof User && $user->can($permission);
    }
}
