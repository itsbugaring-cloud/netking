<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // inv_lokasi: ganti pic_nama (varchar) → tambah pic_user_id FK
        Schema::table('inv_lokasi', function (Blueprint $table) {
            $table->unsignedBigInteger('pic_user_id')->nullable()->after('pic_nama');
            $table->foreign('pic_user_id')->references('id')->on('users')->nullOnDelete();
        });

        // inv_units: tambah penanggung_jawab_user_id FK
        Schema::table('inv_units', function (Blueprint $table) {
            $table->unsignedBigInteger('penanggung_jawab_user_id')->nullable()->after('penanggung_jawab');
            $table->foreign('penanggung_jawab_user_id')->references('id')->on('users')->nullOnDelete();
        });

        // inv_kabel: tambah penanggung_jawab_user_id FK
        Schema::table('inv_kabel', function (Blueprint $table) {
            $table->unsignedBigInteger('penanggung_jawab_user_id')->nullable()->after('penanggung_jawab');
            $table->foreign('penanggung_jawab_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inv_lokasi', function (Blueprint $table) {
            $table->dropForeign(['pic_user_id']);
            $table->dropColumn('pic_user_id');
        });
        Schema::table('inv_units', function (Blueprint $table) {
            $table->dropForeign(['penanggung_jawab_user_id']);
            $table->dropColumn('penanggung_jawab_user_id');
        });
        Schema::table('inv_kabel', function (Blueprint $table) {
            $table->dropForeign(['penanggung_jawab_user_id']);
            $table->dropColumn('penanggung_jawab_user_id');
        });
    }
};
