<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Tipe pesanan boleh kosong sampai pelanggan memilihnya di keranjang.
            $table->string('type', 20)->nullable()->change();

            // Status pemenuhan terpisah dari status pembayaran.
            $table->string('fulfillment_status', 20)->default('pending')->after('status');
            $table->timestamp('accepted_at')->nullable()->after('paid_at');
            $table->timestamp('processing_started_at')->nullable()->after('accepted_at');
            $table->timestamp('fulfilled_at')->nullable()->after('processing_started_at');

            $table->index(['tenant_id', 'outlet_id', 'fulfillment_status'], 'orders_fulfillment_index');
        });

        // Transaksi lama dianggap sudah selesai agar tidak memenuhi antrean baru.
        DB::table('orders')
            ->whereIn('status', ['paid', 'completed'])
            ->update([
                'fulfillment_status' => 'completed',
                'fulfilled_at' => DB::raw('paid_at'),
            ]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_fulfillment_index');
            $table->dropColumn(['fulfillment_status', 'accepted_at', 'processing_started_at', 'fulfilled_at']);
            $table->string('type', 20)->nullable(false)->default('dine_in')->change();
        });
    }
};
