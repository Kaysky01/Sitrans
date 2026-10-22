<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\Tenant\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates a public API request with a tenant API key.
 *
 * The key is sent as the `X-Api-Key` header (or `api_key` query parameter) and
 * resolves the tenant whose active menu/orders the request targets.
 */
class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-Api-Key') ?: $request->query('api_key');

        if (blank($key)) {
            abort(401, 'Kunci API tidak disertakan.');
        }

        $manager = app(TenantManager::class);
        $manager->forget();

        $tenant = $manager->bypass(
            fn (): ?Tenant => Tenant::query()->where('api_key', $key)->first(),
        );

        if ($tenant === null) {
            abort(401, 'Kunci API tidak valid.');
        }

        if (! $tenant->isActive()) {
            abort(403, 'Langganan kafe tidak aktif.');
        }

        $manager->set($tenant);

        return $next($request);
    }
}
