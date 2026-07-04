<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\Materia;
use App\Models\Profesor;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HorarioControllerTest extends TestCase
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

    public function test_users_without_permission_cannot_add_horarios()
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $curso = Curso::factory()->create();
        $materia = Materia::factory()->create();
        $curso->materias()->attach($materia->id);

        $user = User::factory()->create();
        $user->assignRole('Preceptor');
        $this->actingAs($user);

        $this->post(route('cursos.materias.horarios.store', [$curso, $materia]), [
            'dia_semana' => 'lunes',
            'hora_inicio' => '08:00',
            'hora_fin' => '09:00',
        ])->assertForbidden();
    }

    public function test_administrador_can_add_a_horario()
    {
        $this->actingAsAdministrador();
        $curso = Curso::factory()->create();
        $materia = Materia::factory()->create();
        $curso->materias()->attach($materia->id);

        $response = $this->post(route('cursos.materias.horarios.store', [$curso, $materia]), [
            'dia_semana' => 'lunes',
            'hora_inicio' => '08:00',
            'hora_fin' => '09:00',
        ]);

        $response->assertRedirect(route('cursos.edit', $curso));
        $this->assertDatabaseHas('horarios', [
            'dia_semana' => 'lunes',
            'hora_inicio' => '08:00:00',
            'hora_fin' => '09:00:00',
        ]);
    }

    public function test_hora_fin_must_be_after_hora_inicio()
    {
        $this->actingAsAdministrador();
        $curso = Curso::factory()->create();
        $materia = Materia::factory()->create();
        $curso->materias()->attach($materia->id);

        $response = $this->post(route('cursos.materias.horarios.store', [$curso, $materia]), [
            'dia_semana' => 'lunes',
            'hora_inicio' => '09:00',
            'hora_fin' => '08:00',
        ]);

        $response->assertSessionHasErrors('hora_fin');
    }

    public function test_a_curso_cannot_have_two_overlapping_horarios_the_same_day()
    {
        $this->actingAsAdministrador();
        $curso = Curso::factory()->create();
        $musica = Materia::factory()->create();
        $matematica = Materia::factory()->create();
        $curso->materias()->attach($musica->id);
        $curso->materias()->attach($matematica->id);

        $this->post(route('cursos.materias.horarios.store', [$curso, $musica]), [
            'dia_semana' => 'lunes',
            'hora_inicio' => '12:50',
            'hora_fin' => '14:50',
        ]);

        $response = $this->post(route('cursos.materias.horarios.store', [$curso, $matematica]), [
            'dia_semana' => 'lunes',
            'hora_inicio' => '14:00',
            'hora_fin' => '15:00',
        ]);

        $response->assertSessionHasErrors('hora_inicio');
        $this->assertDatabaseMissing('horarios', ['hora_inicio' => '14:00:00']);
    }

    public function test_a_curso_can_have_back_to_back_horarios_the_same_day()
    {
        $this->actingAsAdministrador();
        $curso = Curso::factory()->create();
        $musica = Materia::factory()->create();
        $matematica = Materia::factory()->create();
        $curso->materias()->attach($musica->id);
        $curso->materias()->attach($matematica->id);

        $this->post(route('cursos.materias.horarios.store', [$curso, $musica]), [
            'dia_semana' => 'lunes',
            'hora_inicio' => '12:50',
            'hora_fin' => '14:50',
        ]);

        $response = $this->post(route('cursos.materias.horarios.store', [$curso, $matematica]), [
            'dia_semana' => 'lunes',
            'hora_inicio' => '15:00',
            'hora_fin' => '16:50',
        ]);

        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('horarios', ['hora_inicio' => '15:00:00']);
    }

    public function test_a_profesor_cannot_be_double_booked_across_distinct_cursos()
    {
        $this->actingAsAdministrador();
        $profesor = Profesor::factory()->create();
        $materia = Materia::factory()->create();

        $cursoA = Curso::factory()->create();
        $cursoA->materias()->attach($materia->id, ['profesor_id' => $profesor->id]);
        $this->post(route('cursos.materias.horarios.store', [$cursoA, $materia]), [
            'dia_semana' => 'lunes',
            'hora_inicio' => '08:00',
            'hora_fin' => '09:00',
        ]);

        $cursoB = Curso::factory()->create();
        $otraMateria = Materia::factory()->create();
        $cursoB->materias()->attach($otraMateria->id, ['profesor_id' => $profesor->id]);

        $response = $this->post(route('cursos.materias.horarios.store', [$cursoB, $otraMateria]), [
            'dia_semana' => 'lunes',
            'hora_inicio' => '08:30',
            'hora_fin' => '09:30',
        ]);

        $response->assertSessionHasErrors('hora_inicio');
    }

    public function test_administrador_can_remove_a_horario()
    {
        $this->actingAsAdministrador();
        $curso = Curso::factory()->create();
        $materia = Materia::factory()->create();
        $curso->materias()->attach($materia->id);
        $cursoMateria = $curso->materias()->first()->pivot;
        $horario = $cursoMateria->horarios()->create([
            'dia_semana' => 'lunes',
            'hora_inicio' => '08:00',
            'hora_fin' => '09:00',
        ]);

        $response = $this->delete(route('cursos.materias.horarios.destroy', [$curso, $materia, $horario]));

        $response->assertRedirect(route('cursos.edit', $curso));
        $this->assertDatabaseMissing('horarios', ['id' => $horario->id]);
    }

    public function test_destroy_404s_for_a_horario_not_belonging_to_the_curso_materia()
    {
        $this->actingAsAdministrador();
        $curso = Curso::factory()->create();
        $materia = Materia::factory()->create();
        $curso->materias()->attach($materia->id);

        $otroCurso = Curso::factory()->create();
        $otraMateria = Materia::factory()->create();
        $otroCurso->materias()->attach($otraMateria->id);
        $cursoMateriaAjena = $otroCurso->materias()->first()->pivot;
        $horarioAjeno = $cursoMateriaAjena->horarios()->create([
            'dia_semana' => 'martes',
            'hora_inicio' => '10:00',
            'hora_fin' => '11:00',
        ]);

        $this->delete(route('cursos.materias.horarios.destroy', [$curso, $materia, $horarioAjeno]))
            ->assertNotFound();
    }
}
