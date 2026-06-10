<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('is_free')->default(false)->after('status');
        });

        // Migrate existing "gratis" customers (package_price was set to 0 by import command)
        // We identify them by the status being active + package_price = 0 + having a package
        // But safer: just mark the ones we know from the import (they have specific pppoe_users)
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('is_free');
        });
    }
};
