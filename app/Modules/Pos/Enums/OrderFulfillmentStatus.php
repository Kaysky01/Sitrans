<?php

namespace App\Modules\Pos\Enums;

/**
 * Status operasional pesanan setelah dibayar (atau saat menunggu pembayaran).
 * Dipisahkan dari {@see OrderStatus} agar alur dapur/antrean tidak mengubah
 * status keuangan transaksi.
 */
enum OrderFulfillmentStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Processing = 'processing';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pesanan Baru',
            self::Accepted => 'Diterima',
            self::Processing => 'Diproses',
            self::Completed => 'Selesai',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu dikonfirmasi oleh staf.',
            self::Accepted => 'Pesanan sudah diterima dan masuk daftar kerja.',
            self::Processing => 'Sedang disiapkan oleh dapur/barista.',
            self::Completed => 'Sudah diserahkan ke pelanggan.',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Accepted => 'info',
            self::Processing => 'primary',
            self::Completed => 'success',
        };
    }

    /**
     * Aksi berikutnya yang boleh dilakukan staf.
     *
     * @return array<int, self>
     */
    public function nextStatuses(): array
    {
        return match ($this) {
            self::Pending => [self::Accepted, self::Processing],
            self::Accepted => [self::Processing],
            self::Processing => [self::Completed],
            self::Completed => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->nextStatuses(), true);
    }
}
