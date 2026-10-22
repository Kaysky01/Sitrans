<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 30)->nullable()->after('email');
            $table->string('avatar_path')->nullable()->after('password');
            $table->boolean('is_super_admin')->default(false)->after('avatar_path');
            $table->foreignId('last_active_tenant_id')->nullable()->after('is_super_admin')
                ->constrained('tenants')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('last_active_tenant_id');
            $table->dropColumn(['phone', 'avatar_path', 'is_super_admin']);
        });
    }
};
