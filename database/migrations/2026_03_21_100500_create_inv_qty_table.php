<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_qty', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_barang_id')->constrained('inv_master_barang')->restrictOnDelete();
            $table->foreignId('lokasi_id')->constrained('inv_lokasi')->restrictOnDelete();
            $table->unsignedInteger('jumlah')->default(0);
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['master_barang_id', 'lokasi_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_qty');
    }
};
