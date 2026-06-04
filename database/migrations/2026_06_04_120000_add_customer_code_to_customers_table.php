<?php

use App\Models\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'customer_code')) {
                $table->string('customer_code', 32)->nullable()->after('username');
                $table->unique('customer_code');
            }
        });

        Customer::query()
            ->whereNull('customer_code')
            ->orderBy('id')
            ->chunkById(200, function ($customers): void {
                foreach ($customers as $customer) {
                    $customer->updateQuietly([
                        'customer_code' => Customer::makeCustomerCode((int) $customer->id),
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'customer_code')) {
                $table->dropUnique(['customer_code']);
                $table->dropColumn('customer_code');
            }
        });
    }
};
