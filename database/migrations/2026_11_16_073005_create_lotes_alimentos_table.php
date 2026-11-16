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
        Schema::create('lotes_alimentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donor_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->foreignId('category_id')
                ->constrained('categorias_alimentos')
                ->restrictOnDelete();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->decimal('cantidad_kg', 10, 2);
            $table->decimal('disponible_kg', 10, 2);
            $table->string('estado_conservacion', 50);
            $table->dateTime('expiracion');
            $table->dateTime('recojo_maximo');
            $table->string('imagen', 255)->nullable();
            $table->string('estado', 30);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotes_alimentos');
    }
};
