<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('olts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('brand')->default('Tenda');
            $table->string('model')->default('TES7001');
            $table->string('ip_address');

            // SNMP
            $table->string('snmp_community')->default('public');
            $table->enum('snmp_version', ['1', '2c', '3'])->default('2c');
            $table->string('snmp_username')->nullable(); // SNMPv3
            $table->string('snmp_auth_pass')->nullable();

            // SSH
            $table->string('ssh_user')->nullable();
            $table->string('ssh_pass')->nullable();
            $table->unsignedSmallInteger('ssh_port')->default(22);
            $table->string('ssh_enable_pass')->nullable();

            // Telnet
            $table->string('telnet_user')->nullable();
            $table->string('telnet_pass')->nullable();
            $table->unsignedSmallInteger('telnet_port')->default(23);

            // REST API
            $table->string('api_url')->nullable();
            $table->string('api_token')->nullable();

            $table->enum('preferred_protocol', ['snmp', 'ssh', 'telnet', 'rest'])->default('ssh');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('olts');
    }
};
