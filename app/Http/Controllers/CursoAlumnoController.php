<?php

namespace App\Http\Controllers;

use App\Enums\EstadoMatricula;
use App\Http\Requests\StoreMatriculaRequest;
use App\Http\Requests\UpdateMatriculaRequest;
use App\Models\Alumno;
use App\Models\Curso;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class CursoAlumnoController extends Controller
{
    /**
     * Matricular an alumno into the curso.
     */
    public function store(StoreMatriculaRequest $request, Curso $curso): RedirectResponse
    {
        $data = $request->validated();

        $curso->alumnos()->attach($data['alumno_id'], [
            'fecha_matriculacion' => $data['fecha_matriculacion'],
            'estado' => EstadoMatricula::Activo,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Alumno matriculado.')]);

        return to_route('cursos.edit', $curso);
    }

    /**
     * Update the estado of an alumno's matrícula.
     */
    public function update(UpdateMatriculaRequest $request, Curso $curso, Alumno $alumno): RedirectResponse
    {
        $curso->alumnos()->updateExistingPivot($alumno->id, [
            'estado' => $request->validated('estado'),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Estado actualizado.')]);

        return to_route('cursos.edit', $curso);
    }

    /**
     * Remove an alumno's matrícula from the curso.
     */
    public function destroy(Curso $curso, Alumno $alumno): RedirectResponse
    {
        $curso->alumnos()->detach($alumno->id);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Matrícula eliminada.')]);

        return to_route('cursos.edit', $curso);
    }
}
