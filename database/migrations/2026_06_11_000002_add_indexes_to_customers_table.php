<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip indexes that already exist
        $indexes = collect(DB::select("SHOW INDEX FROM customers"))->pluck('Key_name')->unique();

        Schema::table('customers', function (Blueprint $table) use ($indexes) {
            if (!$indexes->contains('customers_status_index')) {
                $table->index('status');
            }
            if (!$indexes->contains('customers_area_id_index')) {
                $table->index('area_id');
            }
            if (!$indexes->contains('customers_partner_id_index')) {
                $table->index('partner_id');
            }
            if (!$indexes->contains('customers_name_area_id_index')) {
                $table->index(['name', 'area_id']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['area_id']);
            $table->dropIndex(['partner_id']);
            $table->dropIndex(['name', 'area_id']);
        });
    }
};
