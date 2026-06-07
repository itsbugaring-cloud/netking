<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('router_backups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained('areas')->onDelete('cascade');
            $table->string('filename');
            $table->enum('type', ['binary', 'text']);
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['area_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('router_backups');
    }
};
