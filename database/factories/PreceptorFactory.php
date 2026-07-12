<?php

namespace Database\Factories;

use App\Models\Preceptor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Preceptor>
 */
class PreceptorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'dni' => fake()->unique()->numerify('########'),
            'apellido' => fake()->lastName(),
            'nombre' => fake()->firstName(),
            'fecha_nacimiento' => fake()->dateTimeBetween('-60 years', '-25 years'),
            'telefono' => fake()->phoneNumber(),
            'direccion' => fake()->address(),
            'fecha_ingreso' => fake()->dateTimeBetween('-15 years', 'now'),
            'activo' => true,
        ];
    }
}
