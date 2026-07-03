<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCursoRequest;
use App\Http\Requests\UpdateCursoRequest;
use App\Models\CicloLectivo;
use App\Models\Curso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CursoController extends Controller
{
    /**
     * Display a listing of cursos.
     */
    public function index(Request $request): Response
    {
        $cicloLectivoId = $request->integer('ciclo_lectivo_id') ?: null;

        $cursos = Curso::query()
            ->with('cicloLectivo')
            ->when($cicloLectivoId, fn ($query) => $query->where('ciclo_lectivo_id', $cicloLectivoId))
            ->orderByDesc('ciclo_lectivo_id')
            ->orderBy('anio')
            ->orderBy('division')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('cursos/Index', [
            'cursos' => $cursos,
            'cicloLectivoId' => $cicloLectivoId,
            'ciclosLectivos' => CicloLectivo::orderByDesc('anio')->get(['id', 'anio']),
        ]);
    }

    /**
     * Show the form for creating a new curso.
     */
    public function create(): Response
    {
        return Inertia::render('cursos/Create', [
            'ciclosLectivos' => CicloLectivo::orderByDesc('anio')->get(['id', 'anio']),
        ]);
    }

    /**
     * Store a newly created curso.
     */
    public function store(StoreCursoRequest $request): RedirectResponse
    {
        Curso::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Curso creado.')]);

        return to_route('cursos.index');
    }

    /**
     * Show the form for editing a curso.
     */
    public function edit(Curso $curso): Response
    {
        return Inertia::render('cursos/Edit', [
            'curso' => $curso,
            'ciclosLectivos' => CicloLectivo::orderByDesc('anio')->get(['id', 'anio']),
        ]);
    }

    /**
     * Update the specified curso.
     */
    public function update(UpdateCursoRequest $request, Curso $curso): RedirectResponse
    {
        $curso->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Curso actualizado.')]);

        return to_route('cursos.index');
    }

    /**
     * Remove the specified curso.
     */
    public function destroy(Curso $curso): RedirectResponse
    {
        $curso->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Curso eliminado.')]);

        return to_route('cursos.index');
    }
}
