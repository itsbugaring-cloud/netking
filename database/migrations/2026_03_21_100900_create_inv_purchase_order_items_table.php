<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')->constrained('inv_purchase_orders')->cascadeOnDelete();
            $table->foreignId('master_barang_id')->constrained('inv_master_barang')->restrictOnDelete();
            $table->unsignedInteger('jumlah_pesan');
            $table->unsignedInteger('jumlah_diterima')->default(0);
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_purchase_order_items');
    }
};
