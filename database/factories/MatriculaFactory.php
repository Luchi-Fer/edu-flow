<?php

namespace Database\Factories;

use App\Enums\EstadoMatricula;
use App\Models\Alumno;
use App\Models\Curso;
use App\Models\Matricula;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Matricula>
 */
class MatriculaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'alumno_id' => Alumno::factory(),
            'curso_id' => Curso::factory(),
            'fecha_matriculacion' => fake()->dateTimeBetween('-1 year', 'now'),
            'estado' => EstadoMatricula::Activo,
        ];
    }
}
