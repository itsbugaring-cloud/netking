<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redaman_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 20);          // 'fiber' | 'wireless'
            $table->string('name', 150);         // Label / nama titik
            $table->json('inputs');              // Semua parameter input
            $table->json('results');             // Total loss, margin, status
            $table->text('notes')->nullable();   // Catatan bebas
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redaman_calculations');
    }
};
