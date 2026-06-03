<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiber_routes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete();
            $table->json('coordinates'); // [[lat,lng],[lat,lng],...]
            $table->string('color', 7)->default('#2563eb');
            $table->enum('type', ['backbone', 'distribution', 'drop'])->default('distribution');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiber_routes');
    }
};
