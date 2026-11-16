<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('solicitudes_rescate', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lot_id')
                ->constrained('lotes_alimentos')
                ->restrictOnDelete();
            $table->foreignId('comedor_id')
                ->constrained('comedores_populares')
                ->restrictOnDelete();
            $table->decimal('cantidad_kg', 10, 2);
            $table->string('estado', 30);
            $table->dateTime('respondido_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes_rescate');
    }
};
