<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ODP (Optical Distribution Point) — fiber optic distribution nodes
        Schema::create('odps', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable()->comment('e.g. ODP-A01');
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('address')->nullable();
            $table->integer('max_capacity')->default(8)->comment('Max ODC slots');
            $table->integer('used_capacity')->default(0);
            $table->string('odp_type')->default('mini')->comment('mini, standar, closure');
            $table->enum('status', ['active', 'full', 'maintenance', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Add odp_id to customers for ODP-customer mapping
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('odp_id')->nullable()->after('area_id')->constrained('odps')->nullOnDelete();
            $table->string('odp_port')->nullable()->after('odp_id')->comment('e.g. Port 1-8');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['odp_id']);
            $table->dropColumn(['odp_id', 'odp_port']);
        });
        Schema::dropIfExists('odps');
    }
};
