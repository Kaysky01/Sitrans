<?php

namespace App\Livewire;

use App\Models\Outlet;
use App\Support\Tenant\OutletManager;
use App\Support\Tenant\TenantManager;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Small topbar widget letting a user switch the active outlet.
 *
 * The selection is stored in the session and picked up by the {@see OutletManager}.
 */
class OutletSwitcher extends Component
{
    public ?int $outletId = null;

    public ?string $switchError = null;

    /**
     * The POS toolbar shows the pill even with a single outlet; the back
     * office topbar only shows it when there is something to switch.
     */
    public bool $alwaysShow = false;

    public function mount(): void
    {
        $this->outletId = app(OutletManager::class)->id();
    }

    /**
     * @return Collection<int, Outlet>
     */
    #[Computed]
    public function outlets(): Collection
    {
        return app(TenantManager::class)->current()
            ?->outlets()
            ->where('is_active', true)
            ->orderBy('name')
            ->get() ?? new Collection;
    }

    public function updatedOutletId(mixed $value): void
    {
        $outlet = $this->outlets->firstWhere('id', (int) $value);

        if ($outlet === null) {
            $this->outletId = app(OutletManager::class)->id();
            $this->switchError = 'Outlet tidak ditemukan. Pilih outlet dari daftar.';

            return;
        }

        app(OutletManager::class)->set($outlet);

        $this->redirect(request()->header('Referer') ?: url()->current());
    }

    public function render()
    {
        return view('livewire.outlet-switcher');
    }
}
