<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','partner','finance') NOT NULL DEFAULT 'partner'");
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'finance')->update(['role' => 'admin']);
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','partner') NOT NULL DEFAULT 'partner'");
    }
};
