<?php

namespace Database\Factories;

use App\Models\CicloLectivo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CicloLectivo>
 */
class CicloLectivoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $anio = fake()->unique()->numberBetween(2020, 2035);

        return [
            'anio' => $anio,
            'fecha_inicio' => "{$anio}-03-01",
            'fecha_fin' => "{$anio}-12-15",
            'activo' => false,
        ];
    }

    /**
     * Indicate that the school year is the currently active one.
     */
    public function activo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => true,
        ]);
    }
}
