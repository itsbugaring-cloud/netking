<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->timestamps();
        });

        // Seed defaults
        DB::table('settings')->insert([
            ['key' => 'company_name', 'value' => 'NETKING ISP', 'group' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'company_email', 'value' => 'admin@netking.id', 'group' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'timezone', 'value' => 'Asia/Jakarta', 'group' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'currency', 'value' => 'IDR', 'group' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'language', 'value' => 'id', 'group' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'billing_day', 'value' => '1', 'group' => 'billing', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'late_fee_percent', 'value' => '5', 'group' => 'billing', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'grace_period_days', 'value' => '7', 'group' => 'billing', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'notif_email', 'value' => '1', 'group' => 'notifications', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'notif_whatsapp', 'value' => '0', 'group' => 'notifications', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'notif_sms', 'value' => '0', 'group' => 'notifications', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'fonnte_api_key', 'value' => '', 'group' => 'whatsapp', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'wa_invoice_template', 'value' => 'Halo {name}, invoice #{inv_no} sebesar Rp {amount} sudah jatuh tempo. Silakan lakukan pembayaran.', 'group' => 'whatsapp', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
