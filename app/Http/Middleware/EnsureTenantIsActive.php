<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\Tenant\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks access to the back office when the active tenant's trial has ended or
 * the subscription is suspended, funnelling the user to the subscription page
 * (and still allowing them to log out).
 */
class EnsureTenantIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || $user->is_super_admin) {
            return $next($request);
        }

        $tenant = app(TenantManager::class)->current();

        if ($tenant === null || $tenant->isActive()) {
            return $next($request);
        }

        if ($request->routeIs('filament.app.pages.subscription') || $request->routeIs('filament.app.auth.*')) {
            return $next($request);
        }

        return redirect()->to(route('filament.app.pages.subscription'));
    }
}
