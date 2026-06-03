<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_master_barang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained('inv_kategori')->cascadeOnDelete();
            $table->string('merek', 100);
            $table->string('tipe', 150);
            $table->enum('jenis_penghitungan', ['sn', 'meteran', 'qty']);
            $table->text('deskripsi')->nullable();
            $table->decimal('harga_default', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_master_barang');
    }
};
