<?php

namespace App\Http\Controllers;

use App\Enums\NivelEducativo;
use App\Http\Requests\StoreCursoRequest;
use App\Http\Requests\UpdateCursoRequest;
use App\Models\CicloLectivo;
use App\Models\Curso;
use App\Models\Horario;
use App\Models\Materia;
use App\Models\Profesor;
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
        $user = $request->user();
        $verTodosLosCursos = $user->can('ver-todos-los-cursos');

        $cicloLectivoId = match (true) {
            ! $request->has('ciclo_lectivo_id') => CicloLectivo::where('activo', true)->value('id'),
            $request->input('ciclo_lectivo_id') === 'todos' => null,
            default => $request->integer('ciclo_lectivo_id') ?: null,
        };

        $cursos = Curso::query()
            ->with('cicloLectivo')
            ->when(! $verTodosLosCursos, function ($query) use ($user) {
                $query->whereHas('preceptores', fn ($q) => $q->where('preceptores.id', $user->preceptor?->id));
            })
            ->when($cicloLectivoId, fn ($query) => $query->where('ciclo_lectivo_id', $cicloLectivoId))
            ->orderByDesc('ciclo_lectivo_id')
            ->orderBy('nivel')
            ->orderBy('anio_grado')
            ->orderBy('division')
            ->paginate(24)
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
            'etiquetasAnioPorNivel' => $this->etiquetasAnioPorNivel(),
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
        $curso->load([
            'materias' => fn ($query) => $query->orderBy('nombre'),
            'alumnos' => fn ($query) => $query->orderBy('apellido'),
            'preceptores' => fn ($query) => $query->orderBy('apellido'),
        ]);

        $horariosPorCursoMateria = Horario::whereIn('curso_materia_id', $curso->materias->pluck('pivot.id'))
            ->get()
            ->groupBy('curso_materia_id');

        foreach ($curso->materias as $materia) {
            $materia->pivot->setRelation(
                'horarios',
                $horariosPorCursoMateria->get($materia->pivot->id, collect())
                    ->sortBy(fn (Horario $horario) => [$horario->dia_semana->orden(), $horario->hora_inicio])
                    ->values(),
            );
        }

        $profesorIdsAsignados = $curso->materias->pluck('pivot.profesor_id')->filter()->unique();

        return Inertia::render('cursos/Edit', [
            'curso' => $curso,
            'ciclosLectivos' => CicloLectivo::orderByDesc('anio')->get(['id', 'anio']),
            'materiasDisponibles' => Materia::whereNotIn('id', $curso->materias()->pluck('materia_id'))
                ->orderBy('nombre')
                ->get(['id', 'nombre']),
            'profesoresAsignados' => Profesor::whereIn('id', $profesorIdsAsignados)
                ->get(['id', 'nombre', 'apellido']),
            'etiquetasAnioPorNivel' => $this->etiquetasAnioPorNivel(),
        ]);
    }

    /**
     * Etiquetas del año/grado (1-6) para cada nivel educativo, usadas para poblar
     * el select de "Año" sin duplicar la nomenclatura en el frontend.
     *
     * @return array<string, array<int, string>>
     */
    private function etiquetasAnioPorNivel(): array
    {
        return collect(NivelEducativo::cases())
            ->mapWithKeys(fn (NivelEducativo $nivel) => [$nivel->value => $nivel->etiquetasAnioGrado()])
            ->all();
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
        if ($curso->matriculas()->exists()) {
            return $this->denegarEliminacion(__('No se puede eliminar el curso: tiene alumnos matriculados.'));
        }

        $curso->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Curso eliminado.')]);

        return to_route('cursos.index');
    }
}
