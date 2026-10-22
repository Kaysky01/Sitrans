<?php

namespace App\Http\Middleware;

use App\Support\Tenant\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Runs a request with the tenant global scope disabled.
 *
 * Used exclusively by the super admin panel, where a single request legitimately
 * needs to read and write across every tenant. Never attach this to a
 * tenant-facing panel.
 */
class BypassTenantScope
{
    public function handle(Request $request, Closure $next): Response
    {
        return app(TenantManager::class)->bypass(fn (): Response => $next($request));
    }
}
