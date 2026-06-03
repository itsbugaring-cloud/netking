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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('area_id')->constrained('areas')->onDelete('cascade');
            $table->string('name');
            $table->string('pppoe_user')->unique();
            $table->string('pppoe_pass');
            $table->string('remote_ip')->nullable()->unique();
            $table->string('ont_sn')->nullable();
            $table->enum('status', ['provisioning', 'active', 'suspended', 'failed'])->default('provisioning');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('partner_id');
            $table->index('area_id');
            $table->index('status');
            $table->index('pppoe_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
