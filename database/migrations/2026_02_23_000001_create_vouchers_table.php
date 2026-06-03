<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Voucher Batches (groups of generated vouchers)
        Schema::create('voucher_batches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['hotspot', 'pppoe'])->default('hotspot');
            $table->integer('duration_days')->default(30);
            $table->string('speed_limit')->nullable()->comment('e.g. 10M/10M');
            $table->decimal('price', 12, 2)->default(0);
            $table->string('profile')->default('default')->comment('MikroTik profile name');
            $table->string('prefix')->default('NK')->comment('Code prefix');
            $table->integer('total')->default(0);
            $table->integer('used')->default(0);
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // Individual Vouchers
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('voucher_batches')->cascadeOnDelete();
            $table->string('code', 32)->unique();
            $table->enum('status', ['unused', 'used', 'expired'])->default('unused');
            $table->foreignId('redeemed_by')->nullable()->constrained('customers')->nullOnDelete();
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('voucher_batches');
    }
};
