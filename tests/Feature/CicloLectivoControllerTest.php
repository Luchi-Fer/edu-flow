<?php

namespace Tests\Feature;

use App\Models\CicloLectivo;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CicloLectivoControllerTest extends TestCase
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

    public function test_users_without_permission_cannot_access_ciclos_lectivos()
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Preceptor');
        $this->actingAs($user);

        $this->get(route('ciclos-lectivos.index'))->assertForbidden();
    }

    public function test_administrador_can_list_ciclos_lectivos()
    {
        $this->actingAsAdministrador();
        CicloLectivo::factory()->count(3)->create();

        $this->get(route('ciclos-lectivos.index'))->assertOk();
    }

    public function test_administrador_can_create_a_ciclo_lectivo()
    {
        $this->actingAsAdministrador();

        $response = $this->post(route('ciclos-lectivos.store'), [
            'anio' => 2026,
            'fecha_inicio' => '2026-03-01',
            'fecha_fin' => '2026-12-15',
        ]);

        $response->assertRedirect(route('ciclos-lectivos.index'));
        $this->assertDatabaseHas('ciclos_lectivos', ['anio' => 2026]);
    }

    public function test_creating_a_ciclo_lectivo_with_a_duplicate_anio_fails_validation()
    {
        $this->actingAsAdministrador();
        CicloLectivo::factory()->create(['anio' => 2026]);

        $response = $this->post(route('ciclos-lectivos.store'), [
            'anio' => 2026,
            'fecha_inicio' => '2026-03-01',
            'fecha_fin' => '2026-12-15',
        ]);

        $response->assertSessionHasErrors('anio');
    }

    public function test_activating_a_ciclo_lectivo_deactivates_the_others()
    {
        $this->actingAsAdministrador();
        $activo = CicloLectivo::factory()->activo()->create(['anio' => 2025]);
        $nuevo = CicloLectivo::factory()->create(['anio' => 2026, 'activo' => false]);

        $response = $this->put(route('ciclos-lectivos.update', $nuevo), [
            'anio' => $nuevo->anio,
            'fecha_inicio' => $nuevo->fecha_inicio->toDateString(),
            'fecha_fin' => $nuevo->fecha_fin->toDateString(),
            'activo' => true,
        ]);

        $response->assertRedirect(route('ciclos-lectivos.index'));
        $this->assertDatabaseHas('ciclos_lectivos', ['id' => $nuevo->id, 'activo' => true]);
        $this->assertDatabaseHas('ciclos_lectivos', ['id' => $activo->id, 'activo' => false]);
    }

    public function test_administrador_can_delete_a_ciclo_lectivo()
    {
        $this->actingAsAdministrador();
        $ciclo = CicloLectivo::factory()->create();

        $response = $this->delete(route('ciclos-lectivos.destroy', $ciclo));

        $response->assertRedirect(route('ciclos-lectivos.index'));
        $this->assertDatabaseMissing('ciclos_lectivos', ['id' => $ciclo->id]);
    }
}
