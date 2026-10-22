<?php

namespace App\Modules\Pos\Actions;

use App\Models\User;
use App\Support\Tenant\TenantManager;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Verifies a supervisor (owner/manager) PIN for sensitive POS actions such as
 * void, refund, and price override.
 */
final class VerifySupervisorPinAction
{
    /**
     * @param  array<int, string>  $roles
     *
     * @throws ValidationException
     */
    public function execute(string $pin, array $roles = ['owner', 'manager']): User
    {
        $tenant = app(TenantManager::class)->current();

        if ($tenant === null) {
            throw ValidationException::withMessages(['pin' => 'Konteks kafe tidak ditemukan.']);
        }

        $supervisors = $tenant->users()
            ->wherePivotIn('role', $roles)
            ->wherePivot('is_active', true)
            ->whereNotNull('users.pin')
            ->get();

        foreach ($supervisors as $user) {
            if (Hash::check($pin, (string) $user->pin)) {
                return $user;
            }
        }

        throw ValidationException::withMessages(['pin' => 'PIN supervisor tidak valid.']);
    }
}
