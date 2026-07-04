<?php

namespace Database\Factories;

use App\Enums\EstadoAsistencia;
use App\Models\Asistencia;
use App\Models\Matricula;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asistencia>
 */
class AsistenciaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'matricula_id' => Matricula::factory(),
            'fecha' => fake()->dateTimeBetween('-1 month', 'now'),
            'estado' => EstadoAsistencia::Presente,
            'registrado_por' => null,
        ];
    }
}
