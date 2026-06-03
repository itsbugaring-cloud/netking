<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'billing_start_date')) {
                $table->date('billing_start_date')->nullable()->after('package_price');
                $table->index('billing_start_date');
            }
        });

        // Backfill existing customers using created_at so old customers remain billable.
        DB::table('customers')
            ->whereNull('billing_start_date')
            ->update(['billing_start_date' => DB::raw('DATE(created_at)')]);

        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'base_amount')) {
                $table->decimal('base_amount', 12, 2)->nullable()->after('amount');
            }
            if (!Schema::hasColumn('invoices', 'billed_days')) {
                $table->unsignedTinyInteger('billed_days')->nullable()->after('base_amount');
            }
            if (!Schema::hasColumn('invoices', 'period_days')) {
                $table->unsignedTinyInteger('period_days')->nullable()->after('billed_days');
            }
            if (!Schema::hasColumn('invoices', 'period_month')) {
                $table->unsignedTinyInteger('period_month')->nullable()->after('period_days');
            }
            if (!Schema::hasColumn('invoices', 'period_year')) {
                $table->unsignedSmallInteger('period_year')->nullable()->after('period_month');
            }
            if (!Schema::hasColumn('invoices', 'is_prorated')) {
                $table->boolean('is_prorated')->default(false)->after('period_year');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'is_prorated')) {
                $table->dropColumn('is_prorated');
            }
            if (Schema::hasColumn('invoices', 'period_year')) {
                $table->dropColumn('period_year');
            }
            if (Schema::hasColumn('invoices', 'period_month')) {
                $table->dropColumn('period_month');
            }
            if (Schema::hasColumn('invoices', 'period_days')) {
                $table->dropColumn('period_days');
            }
            if (Schema::hasColumn('invoices', 'billed_days')) {
                $table->dropColumn('billed_days');
            }
            if (Schema::hasColumn('invoices', 'base_amount')) {
                $table->dropColumn('base_amount');
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'billing_start_date')) {
                $table->dropColumn('billing_start_date');
            }
        });
    }
};
