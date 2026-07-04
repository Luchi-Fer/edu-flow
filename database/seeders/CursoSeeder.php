<?php

namespace Database\Seeders;

use App\Enums\NivelEducativo;
use App\Models\Alumno;
use App\Models\CicloLectivo;
use App\Models\Curso;
use App\Models\Materia;
use App\Models\Profesor;
use Illuminate\Database\Seeder;

class CursoSeeder extends Seeder
{
    /**
     * Seed cursos for the active ciclo lectivo, with materias/profesores assigned
     * and alumnos matriculados.
     */
    public function run(): void
    {
        $ciclo = CicloLectivo::where('activo', true)->first();
        $profesores = Profesor::pluck('id');
        $materias = Materia::pluck('id');
        $alumnos = Alumno::pluck('id');

        if (! $ciclo || $profesores->isEmpty() || $materias->isEmpty()) {
            return;
        }

        $profesorIndice = 0;
        $cursos = [];

        foreach (NivelEducativo::cases() as $nivel) {
            foreach (range(1, 6) as $anio) {
                foreach (['A', 'B'] as $division) {
                    $curso = Curso::firstOrCreate(
                        ['ciclo_lectivo_id' => $ciclo->id, 'nivel' => $nivel, 'anio' => $anio, 'division' => $division],
                        ['turno' => $division === 'A' ? 'mañana' : 'tarde'],
                    );

                    foreach ($materias as $materiaId) {
                        $profesorId = $profesores[$profesorIndice % $profesores->count()];
                        $profesorIndice++;

                        $curso->materias()->syncWithoutDetaching([
                            $materiaId => ['profesor_id' => $profesorId],
                        ]);
                    }

                    $cursos[] = $curso;
                }
            }
        }

        if ($alumnos->isEmpty()) {
            return;
        }

        foreach ($alumnos as $indice => $alumnoId) {
            $curso = $cursos[$indice % count($cursos)];

            $curso->alumnos()->syncWithoutDetaching([
                $alumnoId => [
                    'fecha_matriculacion' => $ciclo->fecha_inicio->toDateString(),
                    'estado' => 'activo',
                ],
            ]);
        }
    }
}
