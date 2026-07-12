<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\Materia;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MateriaControllerTest extends TestCase
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

    public function test_users_without_permission_cannot_access_materias()
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Preceptor');
        $this->actingAs($user);

        $this->get(route('materias.index'))->assertForbidden();
    }

    public function test_administrador_can_list_materias()
    {
        $this->actingAsAdministrador();
        Materia::factory()->count(3)->create();

        $this->get(route('materias.index'))->assertOk();
    }

    public function test_administrador_can_create_a_materia()
    {
        $this->actingAsAdministrador();

        $response = $this->post(route('materias.store'), [
            'nombre' => 'Matemática',
            'descripcion' => 'Álgebra y geometría',
        ]);

        $response->assertRedirect(route('materias.index'));
        $this->assertDatabaseHas('materias', ['nombre' => 'Matemática']);
    }

    public function test_creating_a_materia_with_a_duplicate_nombre_fails_validation()
    {
        $this->actingAsAdministrador();
        Materia::factory()->create(['nombre' => 'Matemática']);

        $response = $this->post(route('materias.store'), [
            'nombre' => 'Matemática',
        ]);

        $response->assertSessionHasErrors('nombre');
    }

    public function test_administrador_can_update_a_materia()
    {
        $this->actingAsAdministrador();
        $materia = Materia::factory()->create();

        $response = $this->put(route('materias.update', $materia), [
            'nombre' => 'Nombre actualizado',
            'descripcion' => $materia->descripcion,
        ]);

        $response->assertRedirect(route('materias.index'));
        $this->assertDatabaseHas('materias', [
            'id' => $materia->id,
            'nombre' => 'Nombre actualizado',
        ]);
    }

    public function test_administrador_can_delete_a_materia()
    {
        $this->actingAsAdministrador();
        $materia = Materia::factory()->create();

        $response = $this->delete(route('materias.destroy', $materia));

        $response->assertRedirect(route('materias.index'));
        $this->assertDatabaseMissing('materias', ['id' => $materia->id]);
    }

    public function test_cannot_delete_a_materia_assigned_to_a_curso()
    {
        $this->actingAsAdministrador();
        $materia = Materia::factory()->create();
        $curso = Curso::factory()->create();
        $curso->materias()->attach($materia->id);

        $response = $this->delete(route('materias.destroy', $materia));

        $response->assertRedirect();
        $this->assertDatabaseHas('materias', ['id' => $materia->id]);
    }
}
