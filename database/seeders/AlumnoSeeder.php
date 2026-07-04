<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnoSeeder extends Seeder
{
    /**
     * Seed demo alumnos.
     */
    public function run(): void
    {
        Alumno::factory()->count(40)->create();
    }
}
