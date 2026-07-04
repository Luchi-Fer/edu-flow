<?php

namespace Database\Factories;

use App\Models\CicloLectivo;
use App\Models\Curso;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Curso>
 */
class CursoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ciclo_lectivo_id' => CicloLectivo::factory(),
            'nivel' => fake()->randomElement(['primaria', 'secundaria']),
            'anio' => fake()->numberBetween(1, 6),
            'division' => fake()->randomElement(['A', 'B', 'C']),
            'turno' => fake()->randomElement(['mañana', 'tarde']),
        ];
    }
}
