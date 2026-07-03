<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlumnoControllerTest extends TestCase
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

    public function test_users_without_permission_cannot_access_alumnos()
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Preceptor');
        $this->actingAs($user);

        $this->get(route('alumnos.index'))->assertForbidden();
    }

    public function test_administrador_can_list_alumnos()
    {
        $this->actingAsAdministrador();
        Alumno::factory()->count(3)->create();

        $this->get(route('alumnos.index'))->assertOk();
    }

    public function test_administrador_can_create_an_alumno()
    {
        $this->actingAsAdministrador();

        $response = $this->post(route('alumnos.store'), [
            'dni' => '30111222',
            'apellido' => 'Pérez',
            'nombre' => 'Juan',
            'fecha_nacimiento' => '2010-05-01',
            'fecha_ingreso' => '2024-03-01',
        ]);

        $response->assertRedirect(route('alumnos.index'));
        $this->assertDatabaseHas('alumnos', ['dni' => '30111222']);
    }

    public function test_creating_an_alumno_with_a_duplicate_dni_fails_validation()
    {
        $this->actingAsAdministrador();
        Alumno::factory()->create(['dni' => '30111222']);

        $response = $this->post(route('alumnos.store'), [
            'dni' => '30111222',
            'apellido' => 'Pérez',
            'nombre' => 'Juan',
            'fecha_nacimiento' => '2010-05-01',
            'fecha_ingreso' => '2024-03-01',
        ]);

        $response->assertSessionHasErrors('dni');
    }

    public function test_administrador_can_update_an_alumno()
    {
        $this->actingAsAdministrador();
        $alumno = Alumno::factory()->create(['activo' => true]);

        $response = $this->put(route('alumnos.update', $alumno), [
            'dni' => $alumno->dni,
            'apellido' => $alumno->apellido,
            'nombre' => 'Nombre actualizado',
            'fecha_nacimiento' => $alumno->fecha_nacimiento->toDateString(),
            'fecha_ingreso' => $alumno->fecha_ingreso->toDateString(),
            'activo' => false,
        ]);

        $response->assertRedirect(route('alumnos.index'));
        $this->assertDatabaseHas('alumnos', [
            'id' => $alumno->id,
            'nombre' => 'Nombre actualizado',
            'activo' => false,
        ]);
    }

    public function test_administrador_can_delete_an_alumno()
    {
        $this->actingAsAdministrador();
        $alumno = Alumno::factory()->create();

        $response = $this->delete(route('alumnos.destroy', $alumno));

        $response->assertRedirect(route('alumnos.index'));
        $this->assertDatabaseMissing('alumnos', ['id' => $alumno->id]);
    }
}
