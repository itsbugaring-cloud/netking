<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->renameColumn('vlan_id', 'vlan_pppoe');
        });

        Schema::table('areas', function (Blueprint $table) {
            $table->string('vlan_mgmt', 20)->nullable()->after('vlan_pppoe');
        });
    }

    public function down(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->dropColumn('vlan_mgmt');
        });

        Schema::table('areas', function (Blueprint $table) {
            $table->renameColumn('vlan_pppoe', 'vlan_id');
        });
    }
};
