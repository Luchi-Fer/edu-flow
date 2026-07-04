<?php

namespace Database\Seeders;

use App\Models\CicloLectivo;
use Illuminate\Database\Seeder;

class CicloLectivoSeeder extends Seeder
{
    /**
     * Seed the current, active ciclo lectivo.
     */
    public function run(): void
    {
        CicloLectivo::firstOrCreate(
            ['anio' => 2026],
            [
                'fecha_inicio' => '2026-03-01',
                'fecha_fin' => '2026-12-15',
                'activo' => true,
            ],
        );
    }
}
