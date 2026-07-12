<?php

namespace App\Http\Controllers;

use App\Enums\EstadoAsistencia;
use App\Http\Requests\StoreAlumnoRequest;
use App\Http\Requests\UpdateAlumnoRequest;
use App\Models\Alumno;
use App\Models\Asistencia;
use App\Models\Matricula;
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
     * Display the alumno's details, including their curso history and attendance.
     */
    public function show(Alumno $alumno): Response
    {
        $alumno->load('matriculas.curso.cicloLectivo');

        $historial = $alumno->matriculas
            ->sortByDesc(fn (Matricula $matricula) => $matricula->curso->cicloLectivo->anio)
            ->values()
            ->map(function (Matricula $matricula) {
                $total = Asistencia::where('matricula_id', $matricula->id)->count();
                $presentes = Asistencia::where('matricula_id', $matricula->id)
                    ->where('estado', EstadoAsistencia::Presente)
                    ->count();

                return [
                    'id' => $matricula->id,
                    'ciclo_lectivo' => $matricula->curso->cicloLectivo->anio,
                    'curso' => $matricula->curso->label,
                    'estado' => $matricula->estado->value,
                    'fecha_matriculacion' => $matricula->fecha_matriculacion,
                    'asistencia' => [
                        'total' => $total,
                        'presentes' => $presentes,
                        'porcentaje' => $total > 0 ? (int) round($presentes / $total * 100) : null,
                    ],
                ];
            });

        return Inertia::render('alumnos/Show', [
            'alumno' => $alumno,
            'historial' => $historial,
        ]);
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
        if ($alumno->matriculas()->exists()) {
            return $this->denegarEliminacion(__('No se puede eliminar el alumno: tiene matrículas registradas.'));
        }

        $alumno->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Alumno eliminado.')]);

        return to_route('alumnos.index');
    }
}
