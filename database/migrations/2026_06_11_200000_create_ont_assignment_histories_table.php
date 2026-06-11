<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ont_assignment_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('previous_customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('ont_id')->nullable()->constrained('onts')->nullOnDelete();
            $table->foreignId('inv_unit_id')->nullable()->constrained('inv_units')->nullOnDelete();
            $table->string('serial_number', 100)->index();
            $table->string('action', 40)->default('linked');
            $table->string('source', 60)->default('system');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'created_at']);
            $table->index(['serial_number', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ont_assignment_histories');
    }
};
