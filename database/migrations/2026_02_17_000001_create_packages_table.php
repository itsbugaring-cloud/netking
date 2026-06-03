<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // "Paket 10 Mbps"
            $table->string('code')->unique();          // "PKT-10M"
            $table->integer('speed_down');              // Download in Mbps
            $table->integer('speed_up');                // Upload in Mbps
            $table->decimal('price', 12, 2);           // Monthly price
            $table->enum('type', ['residential', 'business', 'corporate'])->default('residential');
            $table->text('description')->nullable();
            $table->string('mikrotik_profile')->nullable(); // MikroTik PPPoE profile name
            $table->string('radius_group')->nullable();     // RADIUS group name (for future)
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
            $table->index('type');
        });

        // Add package_id FK to customers table
        // NOTE: package_price already exists from 2026_02_08_000002 migration
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('package_id')->nullable()->after('area_id')->constrained('packages')->nullOnDelete();
            if (!Schema::hasColumn('customers', 'phone')) {
                $table->string('phone')->nullable()->after('ont_sn');
            }
            if (!Schema::hasColumn('customers', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
            $table->dropColumn('package_id');
            if (Schema::hasColumn('customers', 'phone')) {
                $table->dropColumn('phone');
            }
            if (Schema::hasColumn('customers', 'address')) {
                $table->dropColumn('address');
            }
        });
        Schema::dropIfExists('packages');
    }
};
