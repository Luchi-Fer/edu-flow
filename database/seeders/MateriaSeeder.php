<?php

namespace Database\Seeders;

use App\Models\Materia;
use Illuminate\Database\Seeder;

class MateriaSeeder extends Seeder
{
    /**
     * Seed the typical subjects of an Argentine secondary school.
     */
    public function run(): void
    {
        $materias = [
            'Matemática',
            'Lengua y Literatura',
            'Historia',
            'Geografía',
            'Biología',
            'Química',
            'Inglés',
            'Educación Física',
            'Tecnología',
            'Educación Artística',
            'Formación Ética y Ciudadana',
        ];

        foreach ($materias as $nombre) {
            Materia::firstOrCreate(['nombre' => $nombre]);
        }
    }
}
