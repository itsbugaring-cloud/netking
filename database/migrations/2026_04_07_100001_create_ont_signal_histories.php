<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ont_signal_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ont_id')->constrained('onts')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->decimal('rx_power', 6, 2)->nullable();
            $table->decimal('tx_power', 6, 2)->nullable();
            $table->enum('quality', ['too_strong', 'good', 'fair', 'weak', 'critical', 'unknown'])->default('unknown');
            $table->enum('source', ['acs_live', 'olt_sync', 'manual'])->default('olt_sync');
            $table->enum('ont_status', ['online', 'offline', 'unknown'])->default('unknown');
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();

            $table->index(['ont_id', 'recorded_at']);
            $table->index(['customer_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ont_signal_histories');
    }
};
