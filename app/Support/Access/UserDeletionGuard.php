<?php

namespace App\Support\Access;

use App\Models\User;
use App\Support\Tenant\TenantManager;

/**
 * Aturan penghapusan akun staff yang dipakai bersama oleh halaman daftar,
 * halaman edit, dan aksi massal. Mengembalikan pesan ramah bila hapus harus
 * ditolak, atau null bila aman dilanjutkan.
 */
final class UserDeletionGuard
{
    /**
     * @param  iterable<User>  $users
     * @return string|null Pesan penolakan (bahasa Indonesia) atau null jika aman.
     */
    public static function rejectReason(iterable $users, ?int $actingUserId = null): ?string
    {
        $tenant = app(TenantManager::class)->current();
        $actingUserId ??= auth()->id();

        $ownerCount = 0;
        $hasSelf = false;
        $hasOwner = false;

        foreach ($users as $user) {
            if ($user->getKey() === $actingUserId) {
                $hasSelf = true;
            }

            $role = $tenant !== null ? $user->roleIn($tenant) : null;

            if ($role === RoleDefinitions::OWNER) {
                $hasOwner = true;
                $ownerCount++;
            }
        }

        if ($hasSelf) {
            return 'Kamu tidak bisa menghapus akunmu sendiri. Minta Owner lain yang menghapusnya.';
        }

        if ($hasOwner && $ownerCount === 1 && $tenant !== null) {
            $totalOwners = (int) $tenant->users()
                ->wherePivot('role', RoleDefinitions::OWNER)
                ->wherePivot('is_active', true)
                ->count();

            if ($totalOwners <= 1) {
                return 'Kafe harus tetap punya minimal satu Owner. Tambahkan Owner lain terlebih dahulu.';
            }
        }

        if ($hasOwner) {
            return 'Akun Owner tidak bisa dihapus dari halaman ini. Nonaktifkan akunnya bila perlu.';
        }

        return null;
    }
}
