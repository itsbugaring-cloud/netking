<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_kabel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_barang_id')->constrained('inv_master_barang')->restrictOnDelete();
            $table->string('id_haspel', 30)->unique();
            $table->decimal('panjang_awal', 10, 2);
            $table->decimal('sisa_panjang', 10, 2);
            $table->decimal('nilai_per_meter', 15, 2)->default(0);
            $table->foreignId('lokasi_id')->nullable()->constrained('inv_lokasi')->nullOnDelete();
            $table->string('penanggung_jawab', 150)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_kabel');
    }
};
