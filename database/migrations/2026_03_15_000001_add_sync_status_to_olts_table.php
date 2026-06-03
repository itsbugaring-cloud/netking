<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('olts', function (Blueprint $table) {
            $table->enum('sync_status', ['idle', 'queued', 'syncing', 'done', 'failed'])
                  ->default('idle')
                  ->after('notes');
            $table->text('sync_message')->nullable()->after('sync_status');
            $table->timestamp('synced_at')->nullable()->after('sync_message');
        });
    }

    public function down(): void
    {
        Schema::table('olts', function (Blueprint $table) {
            $table->dropColumn(['sync_status', 'sync_message', 'synced_at']);
        });
    }
};
