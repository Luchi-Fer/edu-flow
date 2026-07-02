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
        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ciclo_lectivo_id')->constrained('ciclos_lectivos')->restrictOnDelete();
            $table->unsignedTinyInteger('anio');
            $table->string('division', 5);
            $table->string('turno')->nullable();
            $table->timestamps();

            $table->unique(['ciclo_lectivo_id', 'anio', 'division']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};
