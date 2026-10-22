<?php

namespace App\Support\Tenant;

use RuntimeException;

/**
 * Thrown when a tenant-scoped model is queried or created without an active
 * tenant. This turns a silent cross-tenant data leak into a loud failure.
 */
class TenantNotResolvedException extends RuntimeException
{
    public static function forQuery(string $model): self
    {
        return new self(
            "Tidak ada tenant aktif saat mengakses [{$model}]. Jalankan middleware `tenant`, "
            .'atau bungkus kode level-platform dengan TenantManager::bypass().'
        );
    }

    public static function forCreate(string $model): self
    {
        return new self(
            "Tidak ada tenant aktif saat membuat [{$model}]. Set `tenant_id` secara eksplisit, "
            .'atau bungkus kode level-platform dengan TenantManager::bypass().'
        );
    }
}
