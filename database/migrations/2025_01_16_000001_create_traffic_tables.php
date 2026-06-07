<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('traffic_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->unsignedBigInteger('bytes_in')->default(0);
            $table->unsignedBigInteger('bytes_out')->default(0);
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->unique(['customer_id', 'date']);
            $table->index(['date', 'area_id']);
        });

        Schema::create('traffic_monthly', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->date('month');
            $table->unsignedBigInteger('total_bytes_in')->default(0);
            $table->unsignedBigInteger('total_bytes_out')->default(0);
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->unique(['customer_id', 'month']);
            $table->index(['month', 'area_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traffic_monthly');
        Schema::dropIfExists('traffic_daily');
    }
};
