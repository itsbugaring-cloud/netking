<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->index('status');
            $table->index('area_id');
            $table->index('partner_id');
            $table->index(['name', 'area_id']);
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
