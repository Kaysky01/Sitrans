<?php

namespace App\Modules\Pos\Actions;

use App\Exceptions\ValidationError;
use App\Models\User;
use App\Support\Tenant\TenantManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

/**
 * Verifies a supervisor (owner/manager) PIN for sensitive POS actions such as
 * void, refund, and price override. Salah PIN dibatasi maksimal 5 percobaan,
 * lalu dikunci sementara selama 5 menit untuk mencegah tebakan beruntun.
 */
final class VerifySupervisorPinAction
{
    private const MAX_ATTEMPTS = 5;

    private const LOCK_SECONDS = 300;

    /**
     * @param  array<int, string>  $roles
     *
     * @throws ValidationError
     */
    public function execute(string $pin, array $roles = ['owner', 'manager']): User
    {
        $tenant = app(TenantManager::class)->current();

        if ($tenant === null) {
            throw ValidationError::with('pin', 'Konteks kafe tidak ditemukan.', 'KAFE_TIDAK_TERHUBUNG');
        }

        $user = auth()->user();
        $scope = $tenant->getKey().':'.($user?->getKey() ?? 'anon');
        $lockKey = 'supervisor-pin-lock:'.$scope;
        $attemptKey = 'supervisor-pin-attempts:'.$scope;

        if (($lockedUntil = Cache::get($lockKey)) !== null) {
            $remaining = max(0, (int) $lockedUntil - now()->getTimestamp());

            throw ValidationError::with(
                'pin',
                'PIN terkunci sementara karena terlalu banyak percobaan. Coba lagi dalam '.$this->humanSeconds($remaining).'.',
                'PIN_TERKUNCI',
            );
        }

        $supervisors = $tenant->users()
            ->wherePivotIn('role', $roles)
            ->wherePivot('is_active', true)
            ->whereNotNull('users.pin')
            ->get();

        foreach ($supervisors as $supervisor) {
            if (Hash::check($pin, (string) $supervisor->pin)) {
                Cache::forget($attemptKey);

                return $supervisor;
            }
        }

        $attempts = (int) Cache::get($attemptKey, 0) + 1;

        if ($attempts >= self::MAX_ATTEMPTS) {
            $lockedUntil = now()->getTimestamp() + self::LOCK_SECONDS;
            Cache::put($lockKey, $lockedUntil, self::LOCK_SECONDS);
            Cache::forget($attemptKey);

            throw ValidationError::with(
                'pin',
                'PIN terkunci sementara karena terlalu banyak percobaan. Coba lagi dalam '.$this->humanSeconds(self::LOCK_SECONDS).'.',
                'PIN_TERKUNCI',
            );
        }

        Cache::put($attemptKey, $attempts, now()->addMinutes(10));

        $left = self::MAX_ATTEMPTS - $attempts;

        throw ValidationError::with(
            'pin',
            'PIN salah. Sisa percobaan: '.$left.'.',
            'PIN_SALAH',
        );
    }

    private function humanSeconds(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds.' detik';
        }

        $minutes = (int) ceil($seconds / 60);

        return $minutes.' menit';
    }
}
