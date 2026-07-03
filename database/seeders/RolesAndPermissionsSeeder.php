<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Seed the application's roles and permissions.
     */
    public function run(): void
    {
        $permissions = [
            'gestionar-usuarios',
            'gestionar-alumnos',
            'gestionar-profesores',
            'gestionar-materias',
            'gestionar-cursos',
            'ver-cursos',
            'tomar-asistencia',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        Role::findOrCreate('Root');

        $administrador = Role::findOrCreate('Administrador');
        $administrador->syncPermissions([
            'gestionar-usuarios',
            'gestionar-alumnos',
            'gestionar-profesores',
            'gestionar-materias',
            'gestionar-cursos',
            'ver-cursos',
        ]);

        $preceptor = Role::findOrCreate('Preceptor');
        $preceptor->syncPermissions([
            'ver-cursos',
            'tomar-asistencia',
        ]);
    }
}
