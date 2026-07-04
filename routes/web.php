<?php

use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\CicloLectivoController;
use App\Http\Controllers\CursoAlumnoController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\CursoMateriaController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\ProfesorController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::resource('usuarios', UsuarioController::class)
        ->except('show')
        ->middleware('can:gestionar-usuarios');

    Route::resource('alumnos', AlumnoController::class)
        ->except('show')
        ->middleware('can:gestionar-alumnos');

    Route::middleware('can:gestionar-profesores')->group(function () {
        Route::get('profesores/buscar', [ProfesorController::class, 'buscar'])
            ->name('profesores.buscar');
    });

    Route::resource('profesores', ProfesorController::class)
        ->parameters(['profesores' => 'profesor'])
        ->middleware('can:gestionar-profesores');

    Route::resource('materias', MateriaController::class)
        ->except('show')
        ->middleware('can:gestionar-materias');

    Route::resource('ciclos-lectivos', CicloLectivoController::class)
        ->except('show')
        ->parameters(['ciclos-lectivos' => 'ciclo_lectivo'])
        ->middleware('can:gestionar-cursos');

    Route::resource('cursos', CursoController::class)
        ->except(['show', 'index'])
        ->middleware('can:gestionar-cursos');

    Route::middleware('can:gestionar-cursos')->group(function () {
        Route::post('cursos/{curso}/materias', [CursoMateriaController::class, 'store'])
            ->name('cursos.materias.store');
        Route::patch('cursos/{curso}/materias/{materia}', [CursoMateriaController::class, 'update'])
            ->name('cursos.materias.update');
        Route::delete('cursos/{curso}/materias/{materia}', [CursoMateriaController::class, 'destroy'])
            ->name('cursos.materias.destroy');

        Route::get('cursos/{curso}/alumnos-disponibles', [CursoAlumnoController::class, 'disponibles'])
            ->name('cursos.alumnos.disponibles');
        Route::post('cursos/{curso}/alumnos', [CursoAlumnoController::class, 'store'])
            ->name('cursos.alumnos.store');
        Route::patch('cursos/{curso}/alumnos/{alumno}', [CursoAlumnoController::class, 'update'])
            ->name('cursos.alumnos.update');
        Route::delete('cursos/{curso}/alumnos/{alumno}', [CursoAlumnoController::class, 'destroy'])
            ->name('cursos.alumnos.destroy');
    });

    Route::middleware('can:acceder-cursos')->group(function () {
        Route::get('cursos', [CursoController::class, 'index'])->name('cursos.index');
        Route::get('cursos/{curso}/asistencia', [AsistenciaController::class, 'show'])
            ->name('cursos.asistencia.show');
        Route::post('cursos/{curso}/asistencia', [AsistenciaController::class, 'store'])
            ->name('cursos.asistencia.store');
    });
});

require __DIR__.'/settings.php';
