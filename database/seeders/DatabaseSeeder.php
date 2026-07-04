<?php

namespace Database\Seeders;

use App\Models\Profesor;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $root = User::factory()->create([
            'name' => 'Root',
            'email' => 'root@test.com',
        ]);
        $root->assignRole('Root');

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->crearUsuariosConRol('Administrador', 2);
        $this->crearUsuariosConRol('Preceptor', 2);
        $this->crearProfesores(6);

        $this->call(AlumnoSeeder::class);
        $this->call(MateriaSeeder::class);
        $this->call(CicloLectivoSeeder::class);
        $this->call(CursoSeeder::class);
    }

    /**
     * Create demo users with the given role, emailed as {rol}@test.com, {rol}2@test.com, ...
     */
    protected function crearUsuariosConRol(string $rol, int $cantidad): void
    {
        for ($i = 1; $i <= $cantidad; $i++) {
            $email = mb_strtolower($rol).($i > 1 ? $i : '').'@test.com';

            $user = User::factory()->create([
                'name' => fake()->name(),
                'email' => $email,
            ]);

            $user->assignRole($rol);
        }
    }

    /**
     * Create demo profesores (with their associated user account), emailed as profesor@test.com, profesor2@test.com, ...
     */
    protected function crearProfesores(int $cantidad): void
    {
        for ($i = 1; $i <= $cantidad; $i++) {
            $nombre = fake()->firstName();
            $apellido = fake()->lastName();

            $user = User::factory()->create([
                'name' => "{$nombre} {$apellido}",
                'email' => 'profesor'.($i > 1 ? $i : '').'@test.com',
            ]);

            $user->assignRole('Profesor');

            Profesor::create([
                'user_id' => $user->id,
                'dni' => fake()->unique()->numerify('########'),
                'apellido' => $apellido,
                'nombre' => $nombre,
                'fecha_nacimiento' => fake()->dateTimeBetween('-60 years', '-25 years'),
                'telefono' => fake()->phoneNumber(),
                'direccion' => fake()->address(),
                'fecha_ingreso' => fake()->dateTimeBetween('-15 years', 'now'),
                'activo' => true,
            ]);
        }
    }
}
