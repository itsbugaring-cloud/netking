<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('payment_proof_path')->nullable()->after('payment_reference');
            $table->string('payment_proof_original_name')->nullable()->after('payment_proof_path');
            $table->text('payment_proof_notes')->nullable()->after('payment_proof_original_name');
            $table->timestamp('payment_proof_submitted_at')->nullable()->after('payment_proof_notes');
            $table->string('payment_review_status')->default('none')->after('payment_proof_submitted_at');
            $table->timestamp('payment_reviewed_at')->nullable()->after('payment_review_status');

            $table->index('payment_review_status');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['payment_review_status']);
            $table->dropColumn([
                'payment_proof_path',
                'payment_proof_original_name',
                'payment_proof_notes',
                'payment_proof_submitted_at',
                'payment_review_status',
                'payment_reviewed_at',
            ]);
        });
    }
};
