<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\CicloLectivo;
use App\Models\Curso;
use App\Models\Preceptor;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CursoControllerTest extends TestCase
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

    public function test_users_without_permission_cannot_access_cursos()
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('cursos.index'))->assertForbidden();
    }

    public function test_preceptor_can_view_cursos_but_not_create_them()
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Preceptor');
        $this->actingAs($user);

        $this->get(route('cursos.index'))->assertOk();
        $this->get(route('cursos.create'))->assertForbidden();
    }

    public function test_administrador_can_list_cursos()
    {
        $this->actingAsAdministrador();
        Curso::factory()->count(3)->create();

        $this->get(route('cursos.index'))->assertOk();
    }

    public function test_preceptor_only_sees_cursos_assigned_to_them()
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Preceptor');
        $this->actingAs($user);

        $preceptor = Preceptor::factory()->create(['user_id' => $user->id]);
        $cursoAsignado = Curso::factory()->create();
        Curso::factory()->create();
        $preceptor->cursos()->attach($cursoAsignado->id);

        $response = $this->get(route('cursos.index', ['ciclo_lectivo_id' => 'todos']));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->has('cursos.data', 1)
            ->where('cursos.data.0.id', $cursoAsignado->id)
        );
    }

    public function test_administrador_sees_every_curso_regardless_of_preceptor_assignments()
    {
        $this->actingAsAdministrador();
        Curso::factory()->count(3)->create();

        $response = $this->get(route('cursos.index', ['ciclo_lectivo_id' => 'todos']));

        $response->assertInertia(fn (Assert $page) => $page->has('cursos.data', 3));
    }

    public function test_administrador_can_create_a_curso()
    {
        $this->actingAsAdministrador();
        $ciclo = CicloLectivo::factory()->create();

        $response = $this->post(route('cursos.store'), [
            'ciclo_lectivo_id' => $ciclo->id,
            'nivel' => 'primaria',
            'anio_grado' => 1,
            'division' => 'A',
            'turno' => 'mañana',
        ]);

        $response->assertRedirect(route('cursos.index'));
        $this->assertDatabaseHas('cursos', [
            'ciclo_lectivo_id' => $ciclo->id,
            'nivel' => 'primaria',
            'anio_grado' => 1,
            'division' => 'A',
        ]);
    }

    public function test_creating_a_duplicate_curso_in_the_same_ciclo_fails_validation()
    {
        $this->actingAsAdministrador();
        $ciclo = CicloLectivo::factory()->create();
        Curso::factory()->create([
            'ciclo_lectivo_id' => $ciclo->id,
            'nivel' => 'primaria',
            'anio_grado' => 1,
            'division' => 'A',
        ]);

        $response = $this->post(route('cursos.store'), [
            'ciclo_lectivo_id' => $ciclo->id,
            'nivel' => 'primaria',
            'anio_grado' => 1,
            'division' => 'A',
        ]);

        $response->assertSessionHasErrors('anio_grado');
    }

    public function test_the_same_anio_grado_and_division_are_allowed_in_a_different_ciclo_lectivo()
    {
        $this->actingAsAdministrador();
        $cicloA = CicloLectivo::factory()->create(['anio' => 2025]);
        $cicloB = CicloLectivo::factory()->create(['anio' => 2026]);
        Curso::factory()->create([
            'ciclo_lectivo_id' => $cicloA->id,
            'nivel' => 'primaria',
            'anio_grado' => 1,
            'division' => 'A',
        ]);

        $response = $this->post(route('cursos.store'), [
            'ciclo_lectivo_id' => $cicloB->id,
            'nivel' => 'primaria',
            'anio_grado' => 1,
            'division' => 'A',
        ]);

        $response->assertRedirect(route('cursos.index'));
        $this->assertDatabaseHas('cursos', ['ciclo_lectivo_id' => $cicloB->id, 'division' => 'A']);
    }

    public function test_the_same_anio_grado_and_division_are_allowed_in_a_different_nivel()
    {
        $this->actingAsAdministrador();
        $ciclo = CicloLectivo::factory()->create();
        Curso::factory()->create([
            'ciclo_lectivo_id' => $ciclo->id,
            'nivel' => 'primaria',
            'anio_grado' => 1,
            'division' => 'A',
        ]);

        $response = $this->post(route('cursos.store'), [
            'ciclo_lectivo_id' => $ciclo->id,
            'nivel' => 'secundaria',
            'anio_grado' => 1,
            'division' => 'A',
        ]);

        $response->assertRedirect(route('cursos.index'));
        $this->assertDatabaseHas('cursos', [
            'ciclo_lectivo_id' => $ciclo->id,
            'nivel' => 'secundaria',
            'anio_grado' => 1,
            'division' => 'A',
        ]);
    }

    public function test_curso_label_reflects_the_nivel_specific_grado_or_anio_wording()
    {
        $ciclo = CicloLectivo::factory()->create();
        $primaria = Curso::factory()->create(['ciclo_lectivo_id' => $ciclo->id, 'nivel' => 'primaria', 'anio_grado' => 1, 'division' => 'A']);
        $secundaria = Curso::factory()->create(['ciclo_lectivo_id' => $ciclo->id, 'nivel' => 'secundaria', 'anio_grado' => 4, 'division' => 'B']);

        $this->assertSame('1er grado A', $primaria->label);
        $this->assertSame('1er año B', $secundaria->label);
    }

    public function test_administrador_can_update_a_curso()
    {
        $this->actingAsAdministrador();
        $curso = Curso::factory()->create();

        $response = $this->put(route('cursos.update', $curso), [
            'ciclo_lectivo_id' => $curso->ciclo_lectivo_id,
            'nivel' => $curso->nivel->value,
            'anio_grado' => $curso->anio_grado,
            'division' => 'Z',
            'turno' => 'tarde',
        ]);

        $response->assertRedirect(route('cursos.index'));
        $this->assertDatabaseHas('cursos', ['id' => $curso->id, 'division' => 'Z']);
    }

    public function test_administrador_can_delete_a_curso()
    {
        $this->actingAsAdministrador();
        $curso = Curso::factory()->create();

        $response = $this->delete(route('cursos.destroy', $curso));

        $response->assertRedirect(route('cursos.index'));
        $this->assertDatabaseMissing('cursos', ['id' => $curso->id]);
    }

    public function test_cannot_delete_a_curso_with_alumnos_matriculados()
    {
        $this->actingAsAdministrador();
        $curso = Curso::factory()->create();
        $alumno = Alumno::factory()->create();
        $curso->alumnos()->attach($alumno->id, [
            'fecha_matriculacion' => '2026-03-01',
            'estado' => 'activo',
        ]);

        $response = $this->delete(route('cursos.destroy', $curso));

        $response->assertRedirect();
        $this->assertDatabaseHas('cursos', ['id' => $curso->id]);
    }
}
