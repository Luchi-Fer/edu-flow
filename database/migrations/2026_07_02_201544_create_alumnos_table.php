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
        Schema::create('alumnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('dni', 15)->unique();
            $table->string('apellido');
            $table->string('nombre');
            $table->date('fecha_nacimiento');
            $table->string('genero', 1)->nullable();
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();
            $table->string('nombre_tutor')->nullable();
            $table->string('telefono_tutor')->nullable();
            $table->string('email_tutor')->nullable();
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
        Schema::dropIfExists('alumnos');
    }
};
