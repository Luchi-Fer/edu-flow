<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMateriaRequest;
use App\Http\Requests\UpdateMateriaRequest;
use App\Models\Materia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MateriaController extends Controller
{
    /**
     * Display a listing of materias.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();

        $materias = Materia::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('nombre', 'like', "%{$search}%");
            })
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('materias/Index', [
            'materias' => $materias,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new materia.
     */
    public function create(): Response
    {
        return Inertia::render('materias/Create');
    }

    /**
     * Store a newly created materia.
     */
    public function store(StoreMateriaRequest $request): RedirectResponse
    {
        Materia::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Materia creada.')]);

        return to_route('materias.index');
    }

    /**
     * Show the form for editing a materia.
     */
    public function edit(Materia $materia): Response
    {
        return Inertia::render('materias/Edit', [
            'materia' => $materia,
        ]);
    }

    /**
     * Update the specified materia.
     */
    public function update(UpdateMateriaRequest $request, Materia $materia): RedirectResponse
    {
        $materia->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Materia actualizada.')]);

        return to_route('materias.index');
    }

    /**
     * Remove the specified materia.
     */
    public function destroy(Materia $materia): RedirectResponse
    {
        if ($materia->cursos()->exists()) {
            return $this->denegarEliminacion(__('No se puede eliminar la materia: está asignada a uno o más cursos.'));
        }

        $materia->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Materia eliminada.')]);

        return to_route('materias.index');
    }
}
