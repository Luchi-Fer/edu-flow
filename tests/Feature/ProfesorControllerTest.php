<?php

namespace Tests\Feature;

use App\Models\Profesor;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfesorControllerTest extends TestCase
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

    public function test_users_without_permission_cannot_access_profesores()
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Preceptor');
        $this->actingAs($user);

        $this->get(route('profesores.index'))->assertForbidden();
    }

    public function test_administrador_can_list_profesores()
    {
        $this->actingAsAdministrador();
        Profesor::factory()->count(3)->create();

        $this->get(route('profesores.index'))->assertOk();
    }

    public function test_administrador_can_create_a_profesor_with_its_user_account()
    {
        $this->actingAsAdministrador();

        $response = $this->post(route('profesores.store'), [
            'dni' => '30111222',
            'apellido' => 'Gómez',
            'nombre' => 'Ana',
            'fecha_nacimiento' => '1985-05-01',
            'fecha_ingreso' => '2024-03-01',
            'email' => 'ana.gomez@eduflow.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('profesores.index'));
        $this->assertDatabaseHas('profesores', ['dni' => '30111222']);
        $this->assertDatabaseHas('users', ['email' => 'ana.gomez@eduflow.test']);

        $profesor = Profesor::where('dni', '30111222')->first();
        $this->assertSame('ana.gomez@eduflow.test', $profesor->user->email);
    }

    public function test_creating_a_profesor_with_a_duplicate_dni_fails_validation()
    {
        $this->actingAsAdministrador();
        Profesor::factory()->create(['dni' => '30111222']);

        $response = $this->post(route('profesores.store'), [
            'dni' => '30111222',
            'apellido' => 'Gómez',
            'nombre' => 'Ana',
            'fecha_nacimiento' => '1985-05-01',
            'fecha_ingreso' => '2024-03-01',
            'email' => 'ana.gomez@eduflow.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('dni');
        $this->assertDatabaseMissing('users', ['email' => 'ana.gomez@eduflow.test']);
    }

    public function test_creating_a_profesor_with_a_duplicate_email_fails_validation()
    {
        $this->actingAsAdministrador();
        Profesor::factory()->create();
        User::factory()->create(['email' => 'ana.gomez@eduflow.test']);

        $response = $this->post(route('profesores.store'), [
            'dni' => '30111222',
            'apellido' => 'Gómez',
            'nombre' => 'Ana',
            'fecha_nacimiento' => '1985-05-01',
            'fecha_ingreso' => '2024-03-01',
            'email' => 'ana.gomez@eduflow.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_administrador_can_update_a_profesor()
    {
        $this->actingAsAdministrador();
        $profesor = Profesor::factory()->create(['activo' => true]);

        $response = $this->put(route('profesores.update', $profesor), [
            'dni' => $profesor->dni,
            'apellido' => $profesor->apellido,
            'nombre' => 'Nombre actualizado',
            'fecha_nacimiento' => $profesor->fecha_nacimiento->toDateString(),
            'fecha_ingreso' => $profesor->fecha_ingreso->toDateString(),
            'activo' => false,
        ]);

        $response->assertRedirect(route('profesores.index'));
        $this->assertDatabaseHas('profesores', [
            'id' => $profesor->id,
            'nombre' => 'Nombre actualizado',
            'activo' => false,
        ]);
    }

    public function test_administrador_can_delete_a_profesor_and_its_user_account()
    {
        $this->actingAsAdministrador();
        $profesor = Profesor::factory()->create();
        $userId = $profesor->user_id;

        $response = $this->delete(route('profesores.destroy', $profesor));

        $response->assertRedirect(route('profesores.index'));
        $this->assertDatabaseMissing('profesores', ['id' => $profesor->id]);
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }
}
