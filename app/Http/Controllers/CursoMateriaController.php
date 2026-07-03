<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCursoMateriaRequest;
use App\Http\Requests\UpdateCursoMateriaRequest;
use App\Models\Curso;
use App\Models\Materia;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class CursoMateriaController extends Controller
{
    /**
     * Assign a materia (optionally with a profesor) to the curso.
     */
    public function store(StoreCursoMateriaRequest $request, Curso $curso): RedirectResponse
    {
        $data = $request->validated();

        $curso->materias()->attach($data['materia_id'], ['profesor_id' => $data['profesor_id'] ?? null]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Materia asignada.')]);

        return to_route('cursos.edit', $curso);
    }

    /**
     * Update the profesor assigned to a materia of the curso.
     */
    public function update(UpdateCursoMateriaRequest $request, Curso $curso, Materia $materia): RedirectResponse
    {
        $curso->materias()->updateExistingPivot($materia->id, [
            'profesor_id' => $request->validated('profesor_id'),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profesor actualizado.')]);

        return to_route('cursos.edit', $curso);
    }

    /**
     * Remove a materia assignment from the curso.
     */
    public function destroy(Curso $curso, Materia $materia): RedirectResponse
    {
        $curso->materias()->detach($materia->id);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Asignación eliminada.')]);

        return to_route('cursos.edit', $curso);
    }
}
