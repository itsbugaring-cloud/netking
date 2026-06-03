<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Note: pppoe_user unique and remote_ip unique constraints are already 
     * defined in the base customers table migration. area_id and partner_id 
     * foreign keys are also already created by constrained(). This migration
     * only adds the invoice_id FK to commission_logs (after invoices table exists).
     */
    public function up(): void
    {
        // Add invoice_id FK to commission_logs (invoices table now exists)
        if (Schema::hasTable('commission_logs') && Schema::hasTable('invoices')) {
            Schema::table('commission_logs', function (Blueprint $table) {
                if (Schema::hasColumn('commission_logs', 'invoice_id')) {
                    $table->foreign('invoice_id', 'commission_logs_invoice_id_foreign')
                        ->references('id')
                        ->on('invoices')
                        ->onDelete('set null');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('commission_logs')) {
            Schema::table('commission_logs', function (Blueprint $table) {
                $table->dropForeign('commission_logs_invoice_id_foreign');
            });
        }
    }
};
