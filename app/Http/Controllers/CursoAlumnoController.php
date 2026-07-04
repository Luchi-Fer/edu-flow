<?php

namespace App\Http\Controllers;

use App\Enums\EstadoMatricula;
use App\Http\Requests\StoreMatriculaRequest;
use App\Http\Requests\UpdateMatriculaRequest;
use App\Models\Alumno;
use App\Models\Curso;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CursoAlumnoController extends Controller
{
    /**
     * Search activo alumnos not yet matriculados in the curso, for use in async comboboxes.
     */
    public function disponibles(Request $request, Curso $curso): JsonResponse
    {
        $search = $request->string('search')->trim()->toString();

        $alumnos = Alumno::where('activo', true)
            ->whereNotIn('id', $curso->alumnos()->pluck('alumno_id'))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('nombre', 'like', "%{$search}%")
                        ->orWhere('apellido', 'like', "%{$search}%")
                        ->orWhere('dni', 'like', "%{$search}%");
                });
            })
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->limit(20)
            ->get(['id', 'nombre', 'apellido']);

        return response()->json($alumnos);
    }

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
