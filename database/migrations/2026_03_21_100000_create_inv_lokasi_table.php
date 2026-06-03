<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_lokasi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lokasi', 200);
            $table->text('alamat')->nullable();
            $table->enum('jenis', ['gudang_utama', 'pop_distribusi'])->default('gudang_utama');
            $table->string('pic_nama', 150)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_lokasi');
    }
};
