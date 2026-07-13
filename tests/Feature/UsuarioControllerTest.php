<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Preceptor;
use App\Models\Profesor;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UsuarioControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsRoot(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Root');
        $this->actingAs($user);

        return $user;
    }

    public function test_only_root_can_access_usuarios()
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $administrador = User::factory()->create();
        $administrador->assignRole('Administrador');
        $this->actingAs($administrador);

        $this->get(route('usuarios.index'))->assertForbidden();
    }

    public function test_root_can_list_usuarios()
    {
        $this->actingAsRoot();
        User::factory()->create()->assignRole('Administrador');

        $this->get(route('usuarios.index'))->assertOk();
    }

    public function test_root_can_create_a_usuario_with_a_role()
    {
        $this->actingAsRoot();

        $response = $this->post(route('usuarios.store'), [
            'name' => 'Nueva Administradora',
            'email' => 'administradora@eduflow.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'Administrador',
        ]);

        $response->assertRedirect(route('usuarios.index'));
        $usuario = User::where('email', 'administradora@eduflow.test')->firstOrFail();
        $this->assertTrue($usuario->hasRole('Administrador'));
    }

    public function test_roles_with_their_own_crud_cannot_be_assigned_here()
    {
        $this->actingAsRoot();

        $response = $this->post(route('usuarios.store'), [
            'name' => 'Preceptor Intruso',
            'email' => 'intruso@eduflow.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'Preceptor',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'intruso@eduflow.test']);
    }

    public function test_root_can_update_a_usuarios_role_and_password()
    {
        $root = $this->actingAsRoot();
        $usuario = User::factory()->create();
        $usuario->assignRole('Administrador');

        $response = $this->put(route('usuarios.update', $usuario), [
            'name' => $usuario->name,
            'email' => $usuario->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'role' => 'Root',
        ]);

        $response->assertRedirect(route('usuarios.index'));
        $usuario->refresh();
        $this->assertTrue($usuario->hasRole('Root'));
        $this->assertFalse($usuario->hasRole('Administrador'));
        $this->assertTrue(Hash::check('newpassword123', $usuario->password));
        $this->assertNotEquals($root->id, $usuario->id);
    }

    public function test_leaving_password_blank_keeps_the_current_one()
    {
        $this->actingAsRoot();
        $usuario = User::factory()->create(['password' => bcrypt('original-password')]);
        $usuario->assignRole('Administrador');

        $this->put(route('usuarios.update', $usuario), [
            'name' => $usuario->name,
            'email' => $usuario->email,
            'role' => 'Administrador',
        ]);

        $this->assertTrue(Hash::check('original-password', $usuario->fresh()->password));
    }

    public function test_root_cannot_change_their_own_role()
    {
        $root = $this->actingAsRoot();

        $response = $this->put(route('usuarios.update', $root), [
            'name' => $root->name,
            'email' => $root->email,
            'role' => 'Administrador',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertTrue($root->fresh()->hasRole('Root'));
    }

    public function test_root_cannot_delete_their_own_account()
    {
        $root = $this->actingAsRoot();

        $this->delete(route('usuarios.destroy', $root))->assertForbidden();
        $this->assertModelExists($root);
    }

    public function test_root_can_delete_another_usuario()
    {
        $this->actingAsRoot();
        $usuario = User::factory()->create();
        $usuario->assignRole('Administrador');

        $response = $this->delete(route('usuarios.destroy', $usuario));

        $response->assertRedirect(route('usuarios.index'));
        $this->assertModelMissing($usuario);
    }

    public function test_usuarios_linked_to_a_profesor_are_not_manageable_here()
    {
        $this->actingAsRoot();
        $profesor = Profesor::factory()->create();

        $this->get(route('usuarios.edit', $profesor->user))->assertNotFound();
        $this->delete(route('usuarios.destroy', $profesor->user))->assertNotFound();
    }

    public function test_usuarios_linked_to_an_alumno_are_not_manageable_here()
    {
        $this->actingAsRoot();
        $user = User::factory()->create();
        Alumno::factory()->create(['user_id' => $user->id]);

        $this->get(route('usuarios.edit', $user))->assertNotFound();
    }

    public function test_usuarios_linked_to_a_preceptor_are_not_manageable_here()
    {
        $this->actingAsRoot();
        $preceptor = Preceptor::factory()->create();

        $this->get(route('usuarios.edit', $preceptor->user))->assertNotFound();
        $this->delete(route('usuarios.destroy', $preceptor->user))->assertNotFound();
    }

    public function test_index_excludes_usuarios_linked_to_a_profesor_alumno_or_preceptor()
    {
        $this->actingAsRoot();
        $profesor = Profesor::factory()->create();
        $preceptor = Preceptor::factory()->create();

        $response = $this->get(route('usuarios.index'));

        $response->assertInertia(fn (Assert $page) => $page->where(
            'usuarios.data',
            fn (mixed $usuarios) => collect($usuarios)->doesntContain(
                fn (array $u) => in_array($u['id'], [$profesor->user_id, $preceptor->user_id], true),
            ),
        ));
    }
}
