<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use App\Support\Access\RoleDefinitions;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Lets a platform super admin step into a cafe to reproduce a problem. The
 * super admin is logged in as that tenant's owner for the duration of the
 * session; the original identity id is kept in the session so it can be
 * restored. Only super admins may start an impersonation, and the impersonated
 * user cannot reach the platform panel.
 */
class ImpersonationController extends Controller
{
    public function start(Request $request, Tenant $tenant): RedirectResponse
    {
        $admin = $request->user();

        abort_unless($admin?->is_super_admin, 403);
        abort_if($request->session()->has('impersonator_id'), 403, 'Sudah dalam mode masuk sebagai.');

        $target = $tenant->users()
            ->wherePivot('role', RoleDefinitions::OWNER)
            ->wherePivot('is_active', true)
            ->orderBy('tenant_user.id')
            ->first()
            ?? $tenant->users()->wherePivot('is_active', true)->orderBy('tenant_user.id')->first();

        if ($target === null) {
            return redirect()
                ->route('filament.admin.resources.tenants.index')
                ->with('impersonation_error', 'Kafe ini belum memiliki pengguna aktif untuk dimasuki.');
        }

        $request->session()->put('impersonator_id', $admin->getKey());
        $request->session()->put('active_tenant_id', $tenant->getKey());
        $request->session()->regenerate();

        Auth::login($target);

        app(ActivityLogger::class)->log(
            'impersonation.start',
            $admin->name.' masuk sebagai '.$target->name.' di '.$tenant->name,
            $tenant,
            ['target_user_id' => $target->getKey()],
            tenant: $tenant,
            user: $admin,
        );

        return redirect()->route('filament.app.pages.dashboard');
    }

    public function stop(Request $request): RedirectResponse
    {
        $adminId = $request->session()->get('impersonator_id');
        $admin = $adminId !== null ? User::find($adminId) : null;
        $tenant = Tenant::find($request->session()->get('active_tenant_id'));

        abort_unless($adminId !== null, 403);

        $request->session()->forget(['impersonator_id', 'active_tenant_id']);
        $request->session()->regenerate();

        Auth::loginUsingId($adminId);

        if ($tenant !== null && $admin !== null) {
            app(ActivityLogger::class)->log(
                'impersonation.stop',
                $admin->name.' kembali ke panel platform',
                $tenant,
                tenant: $tenant,
                user: $admin,
            );
        }

        return redirect()->route('filament.admin.pages.dashboard');
    }
}
