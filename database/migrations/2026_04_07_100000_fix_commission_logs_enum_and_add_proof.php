<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Fix enum: add 'pending' status (was missing, used throughout codebase)
        DB::statement("ALTER TABLE commission_logs MODIFY COLUMN status ENUM('pending','unpaid','paid') DEFAULT 'unpaid'");

        // 2. Add payment proof columns for commission disbursement evidence
        Schema::table('commission_logs', function (Blueprint $table) {
            $table->string('payment_proof')->nullable()->after('paid_at')->comment('File path for transfer proof');
            $table->string('payment_method')->nullable()->after('payment_proof')->comment('e.g. BCA Transfer, QRIS');
            $table->text('payment_notes')->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('commission_logs', function (Blueprint $table) {
            $table->dropColumn(['payment_proof', 'payment_method', 'payment_notes']);
        });
        DB::statement("ALTER TABLE commission_logs MODIFY COLUMN status ENUM('unpaid','paid') DEFAULT 'unpaid'");
    }
};
