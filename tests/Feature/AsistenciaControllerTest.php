<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Curso;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AsistenciaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsPreceptor(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Preceptor');
        $this->actingAs($user);

        return $user;
    }

    protected function matricularAlumno(Curso $curso, string $estado = 'activo'): Alumno
    {
        $alumno = Alumno::factory()->create();

        $curso->alumnos()->attach($alumno->id, [
            'fecha_matriculacion' => '2026-03-01',
            'estado' => $estado,
        ]);

        return $alumno;
    }

    public function test_users_without_permission_cannot_access_asistencia()
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $curso = Curso::factory()->create();

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('cursos.asistencia.show', $curso))->assertForbidden();
    }

    public function test_preceptor_can_view_cursos_but_not_manage_them()
    {
        $this->actingAsPreceptor();
        $curso = Curso::factory()->create();

        $this->get(route('cursos.index'))->assertOk();
        $this->get(route('cursos.create'))->assertForbidden();
        $this->get(route('cursos.edit', $curso))->assertForbidden();
        $this->delete(route('cursos.destroy', $curso))->assertForbidden();
    }

    public function test_preceptor_can_view_the_asistencia_sheet()
    {
        $this->actingAsPreceptor();
        $curso = Curso::factory()->create();
        $alumno = $this->matricularAlumno($curso);

        $this->get(route('cursos.asistencia.show', $curso))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('cursos/Asistencia')
                ->where('alumnos.0.alumno.id', $alumno->id)
                ->where('alumnos.0.estado', 'presente'),
            );
    }

    public function test_only_alumnos_with_active_matricula_appear_in_the_sheet()
    {
        $this->actingAsPreceptor();
        $curso = Curso::factory()->create();
        $this->matricularAlumno($curso, 'baja');

        $this->get(route('cursos.asistencia.show', $curso))
            ->assertInertia(fn (Assert $page) => $page
                ->component('cursos/Asistencia')
                ->where('alumnos', []),
            );
    }

    public function test_preceptor_can_save_the_asistencia_sheet()
    {
        $user = $this->actingAsPreceptor();
        $curso = Curso::factory()->create();
        $alumno = $this->matricularAlumno($curso);
        $matricula = $curso->matriculas()->where('alumno_id', $alumno->id)->first();

        $response = $this->post(route('cursos.asistencia.store', $curso), [
            'fecha' => '2026-07-04',
            'estados' => [
                $matricula->id => 'ausente',
            ],
        ]);

        $response->assertRedirect(route('cursos.asistencia.show', ['curso' => $curso, 'fecha' => '2026-07-04']));
        $this->assertDatabaseHas('asistencias', [
            'matricula_id' => $matricula->id,
            'fecha' => '2026-07-04',
            'estado' => 'ausente',
            'registrado_por' => $user->id,
        ]);
    }

    public function test_resaving_the_same_date_updates_instead_of_duplicating()
    {
        $this->actingAsPreceptor();
        $curso = Curso::factory()->create();
        $alumno = $this->matricularAlumno($curso);
        $matricula = $curso->matriculas()->where('alumno_id', $alumno->id)->first();

        $this->post(route('cursos.asistencia.store', $curso), [
            'fecha' => '2026-07-04',
            'estados' => [$matricula->id => 'ausente'],
        ]);

        $this->post(route('cursos.asistencia.store', $curso), [
            'fecha' => '2026-07-04',
            'estados' => [$matricula->id => 'presente'],
        ]);

        $this->assertDatabaseCount('asistencias', 1);
        $this->assertDatabaseHas('asistencias', [
            'matricula_id' => $matricula->id,
            'fecha' => '2026-07-04',
            'estado' => 'presente',
        ]);
    }

    public function test_a_previously_saved_estado_is_shown_when_reopening_the_same_date()
    {
        $this->actingAsPreceptor();
        $curso = Curso::factory()->create();
        $alumno = $this->matricularAlumno($curso);
        $matricula = $curso->matriculas()->where('alumno_id', $alumno->id)->first();

        $this->post(route('cursos.asistencia.store', $curso), [
            'fecha' => '2026-07-04',
            'estados' => [$matricula->id => 'ausente'],
        ]);

        $this->get(route('cursos.asistencia.show', ['curso' => $curso, 'fecha' => '2026-07-04']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('alumnos.0.estado', 'ausente'),
            );
    }
}
