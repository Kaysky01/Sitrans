<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipt_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('header')->nullable();
            $table->string('footer')->nullable();
            $table->boolean('show_logo')->default(true);
            $table->boolean('show_tax')->default(true);
            $table->boolean('show_cashier')->default(true);
            $table->unsignedSmallInteger('paper_width')->default(58);
            $table->timestamps();

            $table->unique(['tenant_id', 'outlet_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_settings');
    }
};
