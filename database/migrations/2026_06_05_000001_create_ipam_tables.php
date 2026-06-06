<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // OLTs table (created first as routers reference it)
        Schema::create('ipam_olts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('ip_address')->unique();
            $table->timestamps();
        });

        // Routers table
        Schema::create('ipam_routers', function (Blueprint $table) {
            $table->id();
            $table->string('device_name');
            $table->string('wireguard_ip')->unique();
            $table->string('auth_username')->nullable();
            $table->text('auth_password')->nullable();
            $table->string('auth_source')->nullable();
            $table->string('connection_status')->default('unknown');
            $table->text('last_error')->nullable();
            $table->timestamp('last_scanned_at')->nullable();
            $table->foreignId('mapped_olt_id')->nullable()
                ->constrained('ipam_olts')->nullOnDelete();
            $table->boolean('is_online')->nullable();
            $table->timestamp('last_ping_at')->nullable();
            $table->timestamps();
        });

        // IP Pools table
        Schema::create('ipam_ip_pools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('router_id')
                ->constrained('ipam_routers')->cascadeOnDelete();
            $table->string('pool_name');
            $table->string('ranges');
            $table->timestamps();

            $table->unique(['router_id', 'pool_name']);
        });

        // Router Addresses table
        Schema::create('ipam_router_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('router_id')
                ->constrained('ipam_routers')->cascadeOnDelete();
            $table->string('address');
            $table->string('network');
            $table->string('interface');
            $table->boolean('disabled')->default(false);
            $table->string('comment')->nullable();
            $table->timestamps();
        });

        // Router Routes table
        Schema::create('ipam_router_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('router_id')
                ->constrained('ipam_routers')->cascadeOnDelete();
            $table->string('dst_address');
            $table->string('gateway');
            $table->string('distance')->nullable();
            $table->boolean('disabled')->default(false);
            $table->string('comment')->nullable();
            $table->timestamps();
        });

        // WireGuard Interfaces table
        Schema::create('ipam_wireguard_interfaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('router_id')
                ->constrained('ipam_routers')->cascadeOnDelete();
            $table->string('name');
            $table->string('listen_port')->nullable();
            $table->string('public_key')->nullable();
            $table->boolean('disabled')->default(false);
            $table->string('comment')->nullable();
            $table->timestamps();

            $table->unique(['router_id', 'name']);
        });

        // WireGuard Peers table
        Schema::create('ipam_wireguard_peers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('router_id')
                ->constrained('ipam_routers')->cascadeOnDelete();
            $table->string('interface_name');
            $table->string('public_key');
            $table->string('allowed_address');
            $table->string('endpoint_address')->nullable();
            $table->string('endpoint_port')->nullable();
            $table->boolean('disabled')->default(false);
            $table->string('comment')->nullable();
            $table->timestamps();
        });

        // Subnets table
        Schema::create('ipam_subnets', function (Blueprint $table) {
            $table->id();
            $table->string('network_address')->unique();
            $table->integer('prefix_length');
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->string('vlan_id')->nullable();
            $table->string('location')->nullable();
            $table->timestamps();
        });

        // Audit Logs table (only created_at, no updated_at)
        Schema::create('ipam_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('actor');
            $table->string('action');
            $table->string('target_type');
            $table->string('target_id')->nullable();
            $table->text('detail');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ipam_audit_logs');
        Schema::dropIfExists('ipam_subnets');
        Schema::dropIfExists('ipam_wireguard_peers');
        Schema::dropIfExists('ipam_wireguard_interfaces');
        Schema::dropIfExists('ipam_router_routes');
        Schema::dropIfExists('ipam_router_addresses');
        Schema::dropIfExists('ipam_ip_pools');
        Schema::dropIfExists('ipam_routers');
        Schema::dropIfExists('ipam_olts');
    }
};
