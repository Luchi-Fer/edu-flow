<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\Materia;
use App\Models\Profesor;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CursoMateriaControllerTest extends TestCase
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

    public function test_users_without_permission_cannot_assign_materias()
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $curso = Curso::factory()->create();
        $materia = Materia::factory()->create();

        $user = User::factory()->create();
        $user->assignRole('Preceptor');
        $this->actingAs($user);

        $this->post(route('cursos.materias.store', $curso), [
            'materia_id' => $materia->id,
        ])->assertForbidden();
    }

    public function test_administrador_can_assign_a_materia_without_a_profesor()
    {
        $this->actingAsAdministrador();
        $curso = Curso::factory()->create();
        $materia = Materia::factory()->create();

        $response = $this->post(route('cursos.materias.store', $curso), [
            'materia_id' => $materia->id,
        ]);

        $response->assertRedirect(route('cursos.edit', $curso));
        $this->assertDatabaseHas('curso_materia', [
            'curso_id' => $curso->id,
            'materia_id' => $materia->id,
            'profesor_id' => null,
        ]);
    }

    public function test_administrador_can_assign_a_materia_with_a_profesor()
    {
        $this->actingAsAdministrador();
        $curso = Curso::factory()->create();
        $materia = Materia::factory()->create();
        $profesor = Profesor::factory()->create();

        $this->post(route('cursos.materias.store', $curso), [
            'materia_id' => $materia->id,
            'profesor_id' => $profesor->id,
        ]);

        $this->assertDatabaseHas('curso_materia', [
            'curso_id' => $curso->id,
            'materia_id' => $materia->id,
            'profesor_id' => $profesor->id,
        ]);
    }

    public function test_assigning_the_same_materia_twice_fails_validation()
    {
        $this->actingAsAdministrador();
        $curso = Curso::factory()->create();
        $materia = Materia::factory()->create();
        $curso->materias()->attach($materia->id);

        $response = $this->post(route('cursos.materias.store', $curso), [
            'materia_id' => $materia->id,
        ]);

        $response->assertSessionHasErrors('materia_id');
    }

    public function test_administrador_can_update_the_profesor_of_an_assignment()
    {
        $this->actingAsAdministrador();
        $curso = Curso::factory()->create();
        $materia = Materia::factory()->create();
        $curso->materias()->attach($materia->id);
        $profesor = Profesor::factory()->create();

        $response = $this->patch(route('cursos.materias.update', [$curso, $materia]), [
            'profesor_id' => $profesor->id,
        ]);

        $response->assertRedirect(route('cursos.edit', $curso));
        $this->assertDatabaseHas('curso_materia', [
            'curso_id' => $curso->id,
            'materia_id' => $materia->id,
            'profesor_id' => $profesor->id,
        ]);
    }

    public function test_administrador_can_remove_an_assignment()
    {
        $this->actingAsAdministrador();
        $curso = Curso::factory()->create();
        $materia = Materia::factory()->create();
        $curso->materias()->attach($materia->id);

        $response = $this->delete(route('cursos.materias.destroy', [$curso, $materia]));

        $response->assertRedirect(route('cursos.edit', $curso));
        $this->assertDatabaseMissing('curso_materia', [
            'curso_id' => $curso->id,
            'materia_id' => $materia->id,
        ]);
    }
}
