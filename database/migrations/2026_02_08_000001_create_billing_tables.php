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
        // Invoices table
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // INV/NK/YYYYMM/001
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['unpaid', 'paid', 'cancelled'])->default('unpaid');
            $table->string('payment_method')->nullable(); // tripay/midtrans/cash
            $table->string('payment_url')->nullable(); // Payment link from gateway
            $table->string('payment_reference')->nullable(); // Gateway reference ID
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->index('invoice_number');
            $table->index(['customer_id', 'status']);
            $table->index('due_date');
        });

        // Payment Gateways configuration
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('provider'); // tripay, midtrans
            $table->string('merchant_code')->nullable();
            $table->text('api_key');
            $table->text('private_key');
            $table->string('callback_url')->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('config')->nullable(); // Additional configuration
            $table->timestamps();
            
            $table->index('provider');
        });

        // Activity Logs for audit trail
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action'); // create, update, delete, login, payment
            $table->string('model_type')->nullable(); // Invoice, Customer, User
            $table->unsignedBigInteger('model_id')->nullable();
            $table->text('description');
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('changes')->nullable(); // Before/after values
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('action');
            $table->index(['model_type', 'model_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('payment_gateways');
        Schema::dropIfExists('invoices');
    }
};
