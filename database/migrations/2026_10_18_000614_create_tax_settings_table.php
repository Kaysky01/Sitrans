<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id')->nullable()->constrained()->cascadeOnDelete();
            $table->boolean('pb1_enabled')->default(true);
            $table->decimal('pb1_rate', 5, 2)->default(10.00);
            $table->boolean('service_enabled')->default(false);
            $table->decimal('service_rate', 5, 2)->default(5.00);
            $table->boolean('prices_include_tax')->default(false);
            $table->timestamps();

            $table->unique(['tenant_id', 'outlet_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_settings');
    }
};
