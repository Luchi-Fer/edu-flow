<?php

namespace Database\Factories;

use App\Models\Materia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Materia>
 */
class MateriaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->randomElement([
                'Matemática', 'Lengua y Literatura', 'Ciencias Naturales', 'Ciencias Sociales',
                'Educación Física', 'Inglés', 'Educación Artística', 'Tecnología', 'Formación Ética',
            ]),
            'descripcion' => fake()->optional()->sentence(),
        ];
    }
}
