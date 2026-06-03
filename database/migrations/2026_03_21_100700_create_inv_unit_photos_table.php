<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_unit_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('inv_units')->cascadeOnDelete();
            $table->string('path', 500);
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_unit_photos');
    }
};
