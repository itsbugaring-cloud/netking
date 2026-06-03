<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique('customers_pppoe_user_unique');
            $table->unique(['area_id', 'pppoe_user'], 'customers_area_id_pppoe_user_unique');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique('customers_area_id_pppoe_user_unique');
            $table->unique('pppoe_user', 'customers_pppoe_user_unique');
        });
    }
};

