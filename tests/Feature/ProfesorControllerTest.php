<?php

namespace Tests\Feature;

use App\Models\CicloLectivo;
use App\Models\Curso;
use App\Models\Materia;
use App\Models\Profesor;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
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

    public function test_users_without_permission_cannot_search_profesores()
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Preceptor');
        $this->actingAs($user);

        $this->get(route('profesores.buscar'))->assertForbidden();
    }

    public function test_buscar_excludes_inactive_profesores()
    {
        $this->actingAsAdministrador();
        $activo = Profesor::factory()->create(['activo' => true]);
        $inactivo = Profesor::factory()->create(['activo' => false]);

        $response = $this->get(route('profesores.buscar'));

        $response->assertOk();
        $ids = collect($response->json())->pluck('id');
        $this->assertTrue($ids->contains($activo->id));
        $this->assertFalse($ids->contains($inactivo->id));
    }

    public function test_buscar_filters_by_search_term()
    {
        $this->actingAsAdministrador();
        $matematica = Profesor::factory()->create(['activo' => true, 'nombre' => 'Ana', 'apellido' => 'Gomez']);
        $quimica = Profesor::factory()->create(['activo' => true, 'nombre' => 'Juan', 'apellido' => 'Perez']);

        $response = $this->get(route('profesores.buscar').'?search=Gomez');

        $ids = collect($response->json())->pluck('id');
        $this->assertTrue($ids->contains($matematica->id));
        $this->assertFalse($ids->contains($quimica->id));
    }

    public function test_buscar_limits_results_to_twenty()
    {
        $this->actingAsAdministrador();
        Profesor::factory()->count(25)->create(['activo' => true]);

        $response = $this->get(route('profesores.buscar'));

        $this->assertCount(20, $response->json());
    }

    public function test_administrador_can_view_a_profesors_assignments()
    {
        $this->actingAsAdministrador();
        $profesor = Profesor::factory()->create();
        $ciclo = CicloLectivo::factory()->create(['anio' => 2026]);
        $cursoA = Curso::factory()->create(['ciclo_lectivo_id' => $ciclo->id, 'nivel' => 'secundaria', 'anio' => 4, 'division' => 'A']);
        $cursoB = Curso::factory()->create(['ciclo_lectivo_id' => $ciclo->id, 'nivel' => 'primaria', 'anio' => 5, 'division' => 'A']);
        $matematica = Materia::factory()->create(['nombre' => 'Matemática']);
        $quimica = Materia::factory()->create(['nombre' => 'Química']);

        $cursoA->materias()->attach($matematica->id, ['profesor_id' => $profesor->id]);
        $cursoB->materias()->attach($quimica->id, ['profesor_id' => $profesor->id]);

        $response = $this->get(route('profesores.show', $profesor));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('profesores/Show')
            ->has('asignaciones', 2)
            ->where('asignaciones.0.materia', 'Química')
            ->where('asignaciones.0.curso', '5to grado A')
            ->where('asignaciones.1.materia', 'Matemática')
            ->where('asignaciones.1.curso', '1er año A')
        );
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
