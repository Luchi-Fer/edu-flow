<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAlumnoRequest;
use App\Http\Requests\UpdateAlumnoRequest;
use App\Models\Alumno;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AlumnoController extends Controller
{
    /**
     * Display a listing of alumnos.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();

        $alumnos = Alumno::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('nombre', 'like', "%{$search}%")
                        ->orWhere('apellido', 'like', "%{$search}%")
                        ->orWhere('dni', 'like', "%{$search}%");
                });
            })
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('alumnos/Index', [
            'alumnos' => $alumnos,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new alumno.
     */
    public function create(): Response
    {
        return Inertia::render('alumnos/Create');
    }

    /**
     * Store a newly created alumno.
     */
    public function store(StoreAlumnoRequest $request): RedirectResponse
    {
        Alumno::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Alumno creado.')]);

        return to_route('alumnos.index');
    }

    /**
     * Show the form for editing an alumno.
     */
    public function edit(Alumno $alumno): Response
    {
        return Inertia::render('alumnos/Edit', [
            'alumno' => $alumno,
        ]);
    }

    /**
     * Update the specified alumno.
     */
    public function update(UpdateAlumnoRequest $request, Alumno $alumno): RedirectResponse
    {
        $alumno->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Alumno actualizado.')]);

        return to_route('alumnos.index');
    }

    /**
     * Remove the specified alumno.
     */
    public function destroy(Alumno $alumno): RedirectResponse
    {
        $alumno->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Alumno eliminado.')]);

        return to_route('alumnos.index');
    }
}
