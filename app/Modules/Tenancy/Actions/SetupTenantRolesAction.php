<?php

namespace App\Modules\Tenancy\Actions;

use App\Models\Tenant;
use App\Support\Access\RoleDefinitions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates the global permissions (once) and this tenant's roles with their
 * permission set. Roles are team-scoped to the tenant.
 */
final class SetupTenantRolesAction
{
    public function execute(Tenant $tenant): void
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($tenant->getKey());

        foreach (RoleDefinitions::permissions() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (RoleDefinitions::map() as $role => $permissions) {
            $model = Role::findOrCreate($role, 'web');
            $model->syncPermissions($permissions);
        }

        $registrar->forgetCachedPermissions();
    }
}
