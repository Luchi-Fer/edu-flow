<?php

namespace Tests\Feature;

use App\Models\Preceptor;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreceptorControllerTest extends TestCase
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

    public function test_users_without_permission_cannot_access_preceptores()
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Preceptor');
        $this->actingAs($user);

        $this->get(route('preceptores.index'))->assertForbidden();
    }

    public function test_administrador_can_list_preceptores()
    {
        $this->actingAsAdministrador();
        Preceptor::factory()->count(3)->create();

        $this->get(route('preceptores.index'))->assertOk();
    }

    public function test_users_without_permission_cannot_search_preceptores()
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Preceptor');
        $this->actingAs($user);

        $this->get(route('preceptores.buscar'))->assertForbidden();
    }

    public function test_buscar_excludes_inactive_preceptores()
    {
        $this->actingAsAdministrador();
        $activo = Preceptor::factory()->create(['activo' => true]);
        $inactivo = Preceptor::factory()->create(['activo' => false]);

        $response = $this->get(route('preceptores.buscar'));

        $response->assertOk();
        $ids = collect($response->json())->pluck('id');
        $this->assertTrue($ids->contains($activo->id));
        $this->assertFalse($ids->contains($inactivo->id));
    }

    public function test_buscar_filters_by_search_term()
    {
        $this->actingAsAdministrador();
        $ana = Preceptor::factory()->create(['activo' => true, 'nombre' => 'Ana', 'apellido' => 'Gomez']);
        $juan = Preceptor::factory()->create(['activo' => true, 'nombre' => 'Juan', 'apellido' => 'Perez']);

        $response = $this->get(route('preceptores.buscar').'?search=Gomez');

        $ids = collect($response->json())->pluck('id');
        $this->assertTrue($ids->contains($ana->id));
        $this->assertFalse($ids->contains($juan->id));
    }

    public function test_buscar_limits_results_to_twenty()
    {
        $this->actingAsAdministrador();
        Preceptor::factory()->count(25)->create(['activo' => true]);

        $response = $this->get(route('preceptores.buscar'));

        $this->assertCount(20, $response->json());
    }

    public function test_administrador_can_view_a_preceptor()
    {
        $this->actingAsAdministrador();
        $preceptor = Preceptor::factory()->create();

        $response = $this->get(route('preceptores.show', $preceptor));

        $response->assertOk();
    }

    public function test_administrador_can_create_a_preceptor_with_its_user_account()
    {
        $this->actingAsAdministrador();

        $response = $this->post(route('preceptores.store'), [
            'dni' => '30111222',
            'apellido' => 'Gómez',
            'nombre' => 'Ana',
            'fecha_nacimiento' => '1985-05-01',
            'fecha_ingreso' => '2024-03-01',
            'email' => 'ana.gomez@eduflow.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('preceptores.index'));
        $this->assertDatabaseHas('preceptores', ['dni' => '30111222']);
        $this->assertDatabaseHas('users', ['email' => 'ana.gomez@eduflow.test']);

        $preceptor = Preceptor::where('dni', '30111222')->first();
        $this->assertSame('ana.gomez@eduflow.test', $preceptor->user->email);
        $this->assertTrue($preceptor->user->hasRole('Preceptor'));
    }

    public function test_creating_a_preceptor_with_a_duplicate_dni_fails_validation()
    {
        $this->actingAsAdministrador();
        Preceptor::factory()->create(['dni' => '30111222']);

        $response = $this->post(route('preceptores.store'), [
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

    public function test_creating_a_preceptor_with_a_duplicate_email_fails_validation()
    {
        $this->actingAsAdministrador();
        Preceptor::factory()->create();
        User::factory()->create(['email' => 'ana.gomez@eduflow.test']);

        $response = $this->post(route('preceptores.store'), [
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

    public function test_administrador_can_update_a_preceptor()
    {
        $this->actingAsAdministrador();
        $preceptor = Preceptor::factory()->create(['activo' => true]);

        $response = $this->put(route('preceptores.update', $preceptor), [
            'dni' => $preceptor->dni,
            'apellido' => $preceptor->apellido,
            'nombre' => 'Nombre actualizado',
            'fecha_nacimiento' => $preceptor->fecha_nacimiento->toDateString(),
            'fecha_ingreso' => $preceptor->fecha_ingreso->toDateString(),
            'activo' => false,
        ]);

        $response->assertRedirect(route('preceptores.index'));
        $this->assertDatabaseHas('preceptores', [
            'id' => $preceptor->id,
            'nombre' => 'Nombre actualizado',
            'activo' => false,
        ]);
    }

    public function test_administrador_can_delete_a_preceptor_and_its_user_account()
    {
        $this->actingAsAdministrador();
        $preceptor = Preceptor::factory()->create();
        $userId = $preceptor->user_id;

        $response = $this->delete(route('preceptores.destroy', $preceptor));

        $response->assertRedirect(route('preceptores.index'));
        $this->assertDatabaseMissing('preceptores', ['id' => $preceptor->id]);
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }
}
