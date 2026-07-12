<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\Preceptor;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CursoPreceptorControllerTest extends TestCase
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

    public function test_users_without_permission_cannot_search_preceptores_disponibles()
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $curso = Curso::factory()->create();

        $user = User::factory()->create();
        $user->assignRole('Preceptor');
        $this->actingAs($user);

        $this->get(route('cursos.preceptores.disponibles', $curso))->assertForbidden();
    }

    public function test_disponibles_excludes_assigned_and_inactive_preceptores()
    {
        $this->actingAsAdministrador();
        $curso = Curso::factory()->create();
        $disponible = Preceptor::factory()->create(['activo' => true]);
        $yaAsignado = Preceptor::factory()->create(['activo' => true]);
        $inactivo = Preceptor::factory()->create(['activo' => false]);
        $curso->preceptores()->attach($yaAsignado->id);

        $response = $this->get(route('cursos.preceptores.disponibles', $curso));

        $response->assertOk();
        $ids = collect($response->json())->pluck('id');
        $this->assertTrue($ids->contains($disponible->id));
        $this->assertFalse($ids->contains($yaAsignado->id));
        $this->assertFalse($ids->contains($inactivo->id));
    }

    public function test_disponibles_filters_by_search_term()
    {
        $this->actingAsAdministrador();
        $curso = Curso::factory()->create();
        $juan = Preceptor::factory()->create(['activo' => true, 'nombre' => 'Juan', 'apellido' => 'Perez']);
        $ana = Preceptor::factory()->create(['activo' => true, 'nombre' => 'Ana', 'apellido' => 'Gomez']);

        $response = $this->get(route('cursos.preceptores.disponibles', $curso).'?search=Perez');

        $ids = collect($response->json())->pluck('id');
        $this->assertTrue($ids->contains($juan->id));
        $this->assertFalse($ids->contains($ana->id));
    }

    public function test_users_without_permission_cannot_assign_preceptores()
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $curso = Curso::factory()->create();
        $preceptor = Preceptor::factory()->create();

        $user = User::factory()->create();
        $user->assignRole('Preceptor');
        $this->actingAs($user);

        $this->post(route('cursos.preceptores.store', $curso), [
            'preceptor_id' => $preceptor->id,
        ])->assertForbidden();
    }

    public function test_administrador_can_assign_a_preceptor_to_a_curso()
    {
        $this->actingAsAdministrador();
        $curso = Curso::factory()->create();
        $preceptor = Preceptor::factory()->create();

        $response = $this->post(route('cursos.preceptores.store', $curso), [
            'preceptor_id' => $preceptor->id,
        ]);

        $response->assertRedirect(route('cursos.edit', $curso));
        $this->assertDatabaseHas('curso_preceptor', [
            'curso_id' => $curso->id,
            'preceptor_id' => $preceptor->id,
        ]);
    }

    public function test_assigning_the_same_preceptor_twice_fails_validation()
    {
        $this->actingAsAdministrador();
        $curso = Curso::factory()->create();
        $preceptor = Preceptor::factory()->create();
        $curso->preceptores()->attach($preceptor->id);

        $response = $this->post(route('cursos.preceptores.store', $curso), [
            'preceptor_id' => $preceptor->id,
        ]);

        $response->assertSessionHasErrors('preceptor_id');
    }

    public function test_a_preceptor_can_be_assigned_to_several_cursos()
    {
        $this->actingAsAdministrador();
        $preceptor = Preceptor::factory()->create();
        $cursoA = Curso::factory()->create();
        $cursoB = Curso::factory()->create();

        $this->post(route('cursos.preceptores.store', $cursoA), ['preceptor_id' => $preceptor->id]);
        $this->post(route('cursos.preceptores.store', $cursoB), ['preceptor_id' => $preceptor->id]);

        $this->assertCount(2, $preceptor->cursos()->get());
    }

    public function test_administrador_can_remove_a_preceptor_assignment()
    {
        $this->actingAsAdministrador();
        $curso = Curso::factory()->create();
        $preceptor = Preceptor::factory()->create();
        $curso->preceptores()->attach($preceptor->id);

        $response = $this->delete(route('cursos.preceptores.destroy', [$curso, $preceptor]));

        $response->assertRedirect(route('cursos.edit', $curso));
        $this->assertDatabaseMissing('curso_preceptor', [
            'curso_id' => $curso->id,
            'preceptor_id' => $preceptor->id,
        ]);
    }
}
