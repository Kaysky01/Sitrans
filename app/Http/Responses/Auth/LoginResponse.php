<?php

namespace App\Http\Responses\Auth;

use App\Filament\App\Resources\Orders\OrderResource;
use App\Models\User;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as Responsable;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

/**
 * Role-aware landing page after login. Owners and managers open the back
 * office dashboard, cashiers go straight to the POS and kitchen staff to
 * the order history.
 */
class LoginResponse implements Responsable
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        return redirect()->intended($this->landingUrl(auth()->user()));
    }

    /**
     * The TenantManager lazily resolves the tenant from the session, so the
     * spatie permission checks below always run inside the correct team.
     */
    private function landingUrl(?User $user): string
    {
        if ($user instanceof User) {
            if ($user->can('reports.view')) {
                return route('filament.app.pages.dashboard');
            }

            if ($user->can('pos.use')) {
                return route('pos');
            }

            if ($user->can('orders.view')) {
                return OrderResource::getUrl('index');
            }
        }

        return Filament::getUrl();
    }
}
