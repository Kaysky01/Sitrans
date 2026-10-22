<?php

namespace App\Support\Tenant;

use App\Models\Outlet;

/**
 * Holds the outlet the current session is operating in.
 *
 * Resolved lazily: an explicit selection stored in the session wins, otherwise
 * the tenant's first active outlet is used. Registered as a singleton so the
 * cashier, reporting and settings all agree on the active outlet.
 */
class OutletManager
{
    private ?Outlet $outlet = null;

    private bool $resolved = false;

    public function set(?Outlet $outlet): void
    {
        $this->outlet = $outlet;
        $this->resolved = true;

        if ($outlet !== null) {
            session()->put('active_outlet_id', $outlet->getKey());
        }
    }

    public function current(): ?Outlet
    {
        if ($this->resolved) {
            return $this->outlet;
        }

        $this->resolved = true;

        $tenant = app(TenantManager::class)->current();

        if ($tenant === null) {
            return $this->outlet = null;
        }

        $outlets = $tenant->outlets()->where('is_active', true)->orderBy('id');

        $selectedId = $this->sessionOutletId();

        $this->outlet = ($selectedId !== null ? (clone $outlets)->whereKey($selectedId)->first() : null)
            ?? $outlets->first()
            ?? $tenant->outlets()->orderBy('id')->first();

        return $this->outlet;
    }

    public function id(): ?int
    {
        return $this->current()?->getKey();
    }

    public function forget(): void
    {
        $this->outlet = null;
        $this->resolved = false;
    }

    private function sessionOutletId(): ?int
    {
        if (! app()->bound('session') || ! session()->isStarted()) {
            return null;
        }

        $id = session()->get('active_outlet_id');

        return $id !== null ? (int) $id : null;
    }
}
