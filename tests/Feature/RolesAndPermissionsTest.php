<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolesAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_are_seeded_and_assignable_to_a_user()
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Administrador');

        $this->assertTrue($user->fresh()->hasRole('Administrador'));
        $this->assertTrue($user->can('gestionar-alumnos'));
        $this->assertFalse($user->can('tomar-asistencia'));
    }

    public function test_root_bypasses_permission_checks()
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $root = User::factory()->create();
        $root->assignRole('Root');

        $this->assertTrue($root->can('anything-not-defined'));
    }
}
