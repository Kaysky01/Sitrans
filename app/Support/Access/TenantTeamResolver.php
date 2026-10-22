<?php

namespace App\Support\Access;

use App\Support\Tenant\TenantManager;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Contracts\PermissionsTeamResolver;

class TenantTeamResolver implements PermissionsTeamResolver
{
    protected Model|string|int|null $teamId = null;

    public function setPermissionsTeamId(Model|string|int|null $id): void
    {
        $this->teamId = $id instanceof Model ? $id->getKey() : $id;
    }

    public function getPermissionsTeamId(): string|int|null
    {
        if ($this->teamId !== null) {
            return $this->teamId instanceof Model ? $this->teamId->getKey() : (string) $this->teamId;
        }

        $tenantId = app(TenantManager::class)->id();

        return $tenantId !== null ? (string) $tenantId : null;
    }
}
