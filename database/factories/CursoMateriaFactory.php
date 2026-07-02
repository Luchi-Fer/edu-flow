<?php

namespace Database\Factories;

use App\Models\Curso;
use App\Models\CursoMateria;
use App\Models\Materia;
use App\Models\Profesor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CursoMateria>
 */
class CursoMateriaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'curso_id' => Curso::factory(),
            'materia_id' => Materia::factory(),
            'profesor_id' => Profesor::factory(),
        ];
    }
}
