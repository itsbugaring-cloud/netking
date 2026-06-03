<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('area_ip_pools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained()->cascadeOnDelete();
            $table->string('pool_name')->nullable()->comment('Nama pool di Mikrotik, e.g. pool-internet-1');
            $table->string('ip_pool_start');
            $table->string('ip_pool_end');
            $table->tinyInteger('sort_order')->default(0)->comment('Urutan tampilan, pool pertama = 0');
            $table->timestamps();

            $table->index('area_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('area_ip_pools');
    }
};
