<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHorarioRequest;
use App\Models\Curso;
use App\Models\CursoMateria;
use App\Models\Horario;
use App\Models\Materia;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class HorarioController extends Controller
{
    /**
     * Add a horario slot to a curso's materia assignment.
     */
    public function store(StoreHorarioRequest $request, Curso $curso, Materia $materia): RedirectResponse
    {
        $cursoMateria = CursoMateria::where('curso_id', $curso->id)
            ->where('materia_id', $materia->id)
            ->firstOrFail();

        $cursoMateria->horarios()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Horario agregado.')]);

        return to_route('cursos.edit', $curso);
    }

    /**
     * Remove a horario slot.
     */
    public function destroy(Curso $curso, Materia $materia, Horario $horario): RedirectResponse
    {
        abort_unless(
            $horario->cursoMateria->curso_id === $curso->id && $horario->cursoMateria->materia_id === $materia->id,
            404,
        );

        $horario->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Horario eliminado.')]);

        return to_route('cursos.edit', $curso);
    }
}
