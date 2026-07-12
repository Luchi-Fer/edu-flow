<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Asistencia;
use App\Models\CicloLectivo;
use App\Models\Curso;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
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

    public function test_users_without_permission_cannot_view_an_alumno()
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $alumno = Alumno::factory()->create();

        $user = User::factory()->create();
        $user->assignRole('Preceptor');
        $this->actingAs($user);

        $this->get(route('alumnos.show', $alumno))->assertForbidden();
    }

    public function test_administrador_can_view_an_alumnos_historial_sorted_by_most_recent_ciclo_lectivo()
    {
        $this->actingAsAdministrador();
        $alumno = Alumno::factory()->create();

        $ciclo2025 = CicloLectivo::factory()->create(['anio' => 2025]);
        $ciclo2026 = CicloLectivo::factory()->create(['anio' => 2026]);
        $curso2025 = Curso::factory()->create(['ciclo_lectivo_id' => $ciclo2025->id, 'nivel' => 'primaria', 'anio_grado' => 1]);
        $curso2026 = Curso::factory()->create(['ciclo_lectivo_id' => $ciclo2026->id, 'nivel' => 'primaria', 'anio_grado' => 2]);

        $curso2025->alumnos()->attach($alumno->id, ['fecha_matriculacion' => '2025-03-01', 'estado' => 'egresado']);
        $curso2026->alumnos()->attach($alumno->id, ['fecha_matriculacion' => '2026-03-01', 'estado' => 'activo']);

        $matricula2025 = $alumno->matriculas()->where('curso_id', $curso2025->id)->first();
        Asistencia::factory()->create(['matricula_id' => $matricula2025->id, 'fecha' => '2025-03-10', 'estado' => 'presente']);
        Asistencia::factory()->create(['matricula_id' => $matricula2025->id, 'fecha' => '2025-03-11', 'estado' => 'presente']);
        Asistencia::factory()->create(['matricula_id' => $matricula2025->id, 'fecha' => '2025-03-12', 'estado' => 'ausente']);

        $response = $this->get(route('alumnos.show', $alumno));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('alumnos/Show')
            ->has('historial', 2)
            ->where('historial.0.ciclo_lectivo', 2026)
            ->where('historial.0.estado', 'activo')
            ->where('historial.0.asistencia.porcentaje', null)
            ->where('historial.1.ciclo_lectivo', 2025)
            ->where('historial.1.estado', 'egresado')
            ->where('historial.1.asistencia.total', 3)
            ->where('historial.1.asistencia.presentes', 2)
            ->where('historial.1.asistencia.porcentaje', 67),
        );
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

    public function test_cannot_delete_an_alumno_with_matriculas()
    {
        $this->actingAsAdministrador();
        $alumno = Alumno::factory()->create();
        $curso = Curso::factory()->create();
        $curso->alumnos()->attach($alumno->id, [
            'fecha_matriculacion' => '2026-03-01',
            'estado' => 'activo',
        ]);

        $response = $this->delete(route('alumnos.destroy', $alumno));

        $response->assertRedirect();
        $this->assertDatabaseHas('alumnos', ['id' => $alumno->id]);
    }
}
