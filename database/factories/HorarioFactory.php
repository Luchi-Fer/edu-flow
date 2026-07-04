<?php

namespace Database\Factories;

use App\Models\CursoMateria;
use App\Models\Horario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Horario>
 */
class HorarioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'curso_materia_id' => CursoMateria::factory(),
            'dia_semana' => fake()->randomElement(['lunes', 'martes', 'miercoles', 'jueves', 'viernes']),
            'hora_inicio' => '08:00:00',
            'hora_fin' => '09:00:00',
        ];
    }
}
