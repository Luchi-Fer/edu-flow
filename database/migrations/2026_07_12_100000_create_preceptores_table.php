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
        Schema::create('preceptores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->restrictOnDelete();
            $table->string('dni', 15)->unique();
            $table->string('apellido');
            $table->string('nombre');
            $table->date('fecha_nacimiento');
            $table->string('telefono')->nullable();
            $table->string('direccion')->nullable();
            $table->date('fecha_ingreso');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preceptores');
    }
};
