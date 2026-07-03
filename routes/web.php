<?php

use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\CicloLectivoController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\ProfesorController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::resource('alumnos', AlumnoController::class)
        ->except('show')
        ->middleware('can:gestionar-alumnos');

    Route::resource('profesores', ProfesorController::class)
        ->except('show')
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
        ->except('show')
        ->middleware('can:gestionar-cursos');
});

require __DIR__.'/settings.php';
