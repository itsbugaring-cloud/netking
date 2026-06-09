<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->tinyInteger('periode_bulan');       // 1-12
            $table->smallInteger('periode_tahun');      // e.g. 2025
            $table->decimal('jumlah', 12, 2);
            $table->enum('metode', ['transfer', 'cash']);
            $table->string('rekening_tujuan');          // BRI, BNI, Mandiri, BCA, QRIS, Cash
            $table->string('bukti_path')->nullable();
            $table->string('bukti_original_name')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected']);
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->string('reject_reason')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['customer_id', 'periode_tahun', 'periode_bulan']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
