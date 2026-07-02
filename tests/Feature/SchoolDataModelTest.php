<?php

namespace Tests\Feature;

use App\Enums\EstadoMatricula;
use App\Models\Alumno;
use App\Models\CicloLectivo;
use App\Models\Curso;
use App\Models\Materia;
use App\Models\Matricula;
use App\Models\Profesor;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolDataModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_curso_belongs_to_ciclo_lectivo()
    {
        $ciclo = CicloLectivo::factory()->create(['anio' => 2026]);
        $curso = Curso::factory()->for($ciclo)->create();

        $this->assertTrue($curso->cicloLectivo->is($ciclo));
    }

    public function test_alumno_can_be_matriculado_in_a_curso()
    {
        $alumno = Alumno::factory()->create();
        $curso = Curso::factory()->create();

        $curso->alumnos()->attach($alumno->id, [
            'fecha_matriculacion' => now(),
            'estado' => EstadoMatricula::Activo,
        ]);

        $this->assertCount(1, $curso->fresh()->alumnos);
        $this->assertTrue($alumno->cursos()->first()->is($curso));
        $this->assertSame(EstadoMatricula::Activo, $curso->alumnos->first()->pivot->estado);
    }

    public function test_profesor_can_be_assigned_to_a_curso_materia()
    {
        $profesor = Profesor::factory()->create();
        $curso = Curso::factory()->create();
        $materia = Materia::factory()->create();

        $curso->materias()->attach($materia->id, ['profesor_id' => $profesor->id]);

        $asignacion = $curso->fresh()->materias->first();
        $this->assertSame($profesor->id, $asignacion->pivot->profesor_id);
        $this->assertCount(1, $profesor->asignaciones);
    }

    public function test_matricula_enforces_unique_alumno_per_curso()
    {
        $alumno = Alumno::factory()->create();
        $curso = Curso::factory()->create();
        Matricula::factory()->for($alumno)->for($curso)->create();

        $this->expectException(QueryException::class);
        Matricula::factory()->for($alumno)->for($curso)->create();
    }
}
