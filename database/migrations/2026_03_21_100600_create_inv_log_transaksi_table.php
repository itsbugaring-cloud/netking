<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_log_transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('tipe', [
                'masuk_baru',
                'mutasi',
                'potong_kabel',
                'pasang',
                'retur',
                'barang_keluar',
                'penyesuaian',
            ]);
            $table->string('referensi_tabel', 50)->nullable();
            $table->unsignedBigInteger('referensi_id')->nullable();
            $table->foreignId('lokasi_asal_id')
                ->nullable()
                ->references('id')->on('inv_lokasi')
                ->nullOnDelete();
            $table->foreignId('lokasi_tujuan_id')
                ->nullable()
                ->references('id')->on('inv_lokasi')
                ->nullOnDelete();
            $table->decimal('kuantitas', 10, 2)->default(1);
            $table->text('keterangan')->nullable();
            $table->json('detail_lama')->nullable();
            $table->json('detail_baru')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_log_transaksi');
    }
};
