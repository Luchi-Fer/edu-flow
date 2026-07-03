<?php

namespace Tests\Feature;

use App\Models\CicloLectivo;
use App\Models\Curso;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $user->assignRole('Preceptor');
        $this->actingAs($user);

        $this->get(route('cursos.index'))->assertForbidden();
    }

    public function test_administrador_can_list_cursos()
    {
        $this->actingAsAdministrador();
        Curso::factory()->count(3)->create();

        $this->get(route('cursos.index'))->assertOk();
    }

    public function test_administrador_can_create_a_curso()
    {
        $this->actingAsAdministrador();
        $ciclo = CicloLectivo::factory()->create();

        $response = $this->post(route('cursos.store'), [
            'ciclo_lectivo_id' => $ciclo->id,
            'anio' => 1,
            'division' => 'A',
            'turno' => 'mañana',
        ]);

        $response->assertRedirect(route('cursos.index'));
        $this->assertDatabaseHas('cursos', [
            'ciclo_lectivo_id' => $ciclo->id,
            'anio' => 1,
            'division' => 'A',
        ]);
    }

    public function test_creating_a_duplicate_curso_in_the_same_ciclo_fails_validation()
    {
        $this->actingAsAdministrador();
        $ciclo = CicloLectivo::factory()->create();
        Curso::factory()->create([
            'ciclo_lectivo_id' => $ciclo->id,
            'anio' => 1,
            'division' => 'A',
        ]);

        $response = $this->post(route('cursos.store'), [
            'ciclo_lectivo_id' => $ciclo->id,
            'anio' => 1,
            'division' => 'A',
        ]);

        $response->assertSessionHasErrors('anio');
    }

    public function test_the_same_anio_and_division_are_allowed_in_a_different_ciclo_lectivo()
    {
        $this->actingAsAdministrador();
        $cicloA = CicloLectivo::factory()->create(['anio' => 2025]);
        $cicloB = CicloLectivo::factory()->create(['anio' => 2026]);
        Curso::factory()->create([
            'ciclo_lectivo_id' => $cicloA->id,
            'anio' => 1,
            'division' => 'A',
        ]);

        $response = $this->post(route('cursos.store'), [
            'ciclo_lectivo_id' => $cicloB->id,
            'anio' => 1,
            'division' => 'A',
        ]);

        $response->assertRedirect(route('cursos.index'));
        $this->assertDatabaseHas('cursos', ['ciclo_lectivo_id' => $cicloB->id, 'division' => 'A']);
    }

    public function test_administrador_can_update_a_curso()
    {
        $this->actingAsAdministrador();
        $curso = Curso::factory()->create();

        $response = $this->put(route('cursos.update', $curso), [
            'ciclo_lectivo_id' => $curso->ciclo_lectivo_id,
            'anio' => $curso->anio,
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
}
