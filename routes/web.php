<?php

use App\Http\Controllers\AlumnoController;
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
});

require __DIR__.'/settings.php';
