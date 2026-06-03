<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_po', 30)->unique();
            $table->text('keterangan')->nullable();
            $table->enum('status', ['pending', 'partial', 'selesai'])->default('pending');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_purchase_orders');
    }
};
