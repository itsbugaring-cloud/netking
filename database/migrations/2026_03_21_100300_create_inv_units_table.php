<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_barang_id')->constrained('inv_master_barang')->restrictOnDelete();
            $table->string('serial_number', 100)->unique();
            $table->string('mac_address', 50)->nullable();
            $table->enum('status', [
                'gudang',
                'terpasang',
                'dibawa_teknisi',
                'rusak',
                'rma',
                'terjual',
                'hilang',
            ])->default('gudang');
            $table->decimal('nilai_aset', 15, 2)->default(0);
            $table->foreignId('lokasi_id')->nullable()->constrained('inv_lokasi')->nullOnDelete();
            $table->string('penanggung_jawab', 150)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_units');
    }
};
