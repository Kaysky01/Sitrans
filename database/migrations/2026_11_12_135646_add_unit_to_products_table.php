<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Satuan menentukan apakah stok menu dihitung bulat (porsi, gelas)
     * atau boleh pecahan (kg, liter).
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('unit', 20)->default('porsi')->after('track_stock');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('unit');
        });
    }
};
