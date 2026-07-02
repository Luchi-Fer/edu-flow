<?php

namespace Database\Factories;

use App\Models\Alumno;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alumno>
 */
class AlumnoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'dni' => fake()->unique()->numerify('########'),
            'apellido' => fake()->lastName(),
            'nombre' => fake()->firstName(),
            'fecha_nacimiento' => fake()->dateTimeBetween('-18 years', '-5 years'),
            'genero' => fake()->randomElement(['M', 'F', null]),
            'direccion' => fake()->address(),
            'telefono' => fake()->optional()->phoneNumber(),
            'nombre_tutor' => fake()->name(),
            'telefono_tutor' => fake()->phoneNumber(),
            'email_tutor' => fake()->safeEmail(),
            'fecha_ingreso' => fake()->dateTimeBetween('-5 years', 'now'),
            'activo' => true,
        ];
    }
}
