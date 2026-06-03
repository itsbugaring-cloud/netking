<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('onts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('olt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();

            $table->string('serial_number');
            $table->string('pon_port')->nullable();      // e.g. "0/1"
            $table->integer('olt_port_index')->nullable(); // numeric port index
            $table->string('description')->nullable();   // ONT name/alias

            $table->enum('status', ['online', 'offline', 'unknown'])->default('unknown');
            $table->decimal('rx_power', 6, 2)->nullable(); // dBm received by OLT
            $table->decimal('tx_power', 6, 2)->nullable(); // dBm transmitted by ONT
            $table->integer('distance')->nullable();        // meters
            $table->string('firmware_version')->nullable();
            $table->string('equipment_id')->nullable();

            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['olt_id', 'serial_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onts');
    }
};
