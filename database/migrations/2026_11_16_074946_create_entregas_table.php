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
        Schema::create('entregas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('traslado_id')
                ->unique()
                ->constrained('traslados')
                ->restrictOnDelete();
            $table->foreignId('received_by')
                ->constrained('users')
                ->restrictOnDelete();
            $table->dateTime('recibido_at');
            $table->decimal('cantidad_recibida_kg', 10, 2);
            $table->string('estado_conservacion', 50);
            $table->tinyInteger('calificacion_calidad')->nullable();
            $table->text('comentarios')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entregas');
    }
};
