<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Curso;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CursoAlumnoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsAdministrador(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Administrador');
        $this->actingAs($user);

        return $user;
    }

    public function test_users_without_permission_cannot_matricular_alumnos()
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $curso = Curso::factory()->create();
        $alumno = Alumno::factory()->create();

        $user = User::factory()->create();
        $user->assignRole('Preceptor');
        $this->actingAs($user);

        $this->post(route('cursos.alumnos.store', $curso), [
            'alumno_id' => $alumno->id,
            'fecha_matriculacion' => '2026-03-01',
        ])->assertForbidden();
    }

    public function test_administrador_can_matricular_an_alumno()
    {
        $this->actingAsAdministrador();
        $curso = Curso::factory()->create();
        $alumno = Alumno::factory()->create();

        $response = $this->post(route('cursos.alumnos.store', $curso), [
            'alumno_id' => $alumno->id,
            'fecha_matriculacion' => '2026-03-01',
        ]);

        $response->assertRedirect(route('cursos.edit', $curso));
        $this->assertDatabaseHas('matriculas', [
            'curso_id' => $curso->id,
            'alumno_id' => $alumno->id,
            'estado' => 'activo',
        ]);
    }

    public function test_matriculating_the_same_alumno_twice_fails_validation()
    {
        $this->actingAsAdministrador();
        $curso = Curso::factory()->create();
        $alumno = Alumno::factory()->create();
        $curso->alumnos()->attach($alumno->id, [
            'fecha_matriculacion' => '2026-03-01',
            'estado' => 'activo',
        ]);

        $response = $this->post(route('cursos.alumnos.store', $curso), [
            'alumno_id' => $alumno->id,
            'fecha_matriculacion' => '2026-03-01',
        ]);

        $response->assertSessionHasErrors('alumno_id');
    }

    public function test_administrador_can_update_the_estado_of_a_matricula()
    {
        $this->actingAsAdministrador();
        $curso = Curso::factory()->create();
        $alumno = Alumno::factory()->create();
        $curso->alumnos()->attach($alumno->id, [
            'fecha_matriculacion' => '2026-03-01',
            'estado' => 'activo',
        ]);

        $response = $this->patch(route('cursos.alumnos.update', [$curso, $alumno]), [
            'estado' => 'baja',
        ]);

        $response->assertRedirect(route('cursos.edit', $curso));
        $this->assertDatabaseHas('matriculas', [
            'curso_id' => $curso->id,
            'alumno_id' => $alumno->id,
            'estado' => 'baja',
        ]);
    }

    public function test_administrador_can_remove_a_matricula()
    {
        $this->actingAsAdministrador();
        $curso = Curso::factory()->create();
        $alumno = Alumno::factory()->create();
        $curso->alumnos()->attach($alumno->id, [
            'fecha_matriculacion' => '2026-03-01',
            'estado' => 'activo',
        ]);

        $response = $this->delete(route('cursos.alumnos.destroy', [$curso, $alumno]));

        $response->assertRedirect(route('cursos.edit', $curso));
        $this->assertDatabaseMissing('matriculas', [
            'curso_id' => $curso->id,
            'alumno_id' => $alumno->id,
        ]);
    }
}
