<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Drop the invoices table — replaced by the payments table.
     */
    public function up(): void
    {
        Schema::dropIfExists('invoices');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The invoices table is permanently removed.
        // No rollback — recreate manually if ever needed.
    }
};
