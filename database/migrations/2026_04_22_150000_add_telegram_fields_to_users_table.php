<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'telegram_username')) {
                $table->string('telegram_username', 32)->nullable()->after('area_id');
                $table->unique('telegram_username');
            }

            if (!Schema::hasColumn('users', 'telegram_chat_id')) {
                $table->string('telegram_chat_id', 32)->nullable()->after('telegram_username');
                $table->unique('telegram_chat_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'telegram_chat_id')) {
                $table->dropUnique('users_telegram_chat_id_unique');
                $table->dropColumn('telegram_chat_id');
            }

            if (Schema::hasColumn('users', 'telegram_username')) {
                $table->dropUnique('users_telegram_username_unique');
                $table->dropColumn('telegram_username');
            }
        });
    }
};

