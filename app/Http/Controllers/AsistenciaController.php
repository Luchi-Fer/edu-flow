<?php

namespace App\Http\Controllers;

use App\Enums\EstadoMatricula;
use App\Http\Requests\StoreAsistenciaRequest;
use App\Models\Asistencia;
use App\Models\Curso;
use App\Models\Matricula;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AsistenciaController extends Controller
{
    /**
     * Display the attendance sheet for the curso on a given date.
     */
    public function show(Request $request, Curso $curso): Response
    {
        $fecha = $request->date('fecha')?->toDateString() ?? today()->toDateString();

        $matriculas = Matricula::where('curso_id', $curso->id)
            ->where('estado', EstadoMatricula::Activo)
            ->with('alumno')
            ->get()
            ->sortBy(fn (Matricula $matricula) => $matricula->alumno->apellido);

        $asistencias = Asistencia::where('fecha', $fecha)
            ->whereIn('matricula_id', $matriculas->pluck('id'))
            ->get()
            ->keyBy('matricula_id');

        $alumnos = [];

        foreach ($matriculas as $matricula) {
            $alumnos[] = [
                'matricula_id' => $matricula->id,
                'alumno' => [
                    'id' => $matricula->alumno->id,
                    'nombre' => $matricula->alumno->nombre,
                    'apellido' => $matricula->alumno->apellido,
                ],
                'estado' => $asistencias->get($matricula->id)?->estado->value ?? 'presente',
            ];
        }

        return Inertia::render('cursos/Asistencia', [
            'curso' => $curso,
            'fecha' => $fecha,
            'alumnos' => $alumnos,
        ]);
    }

    /**
     * Save the attendance sheet for the curso on a given date.
     */
    public function store(StoreAsistenciaRequest $request, Curso $curso): RedirectResponse
    {
        $data = $request->validated();

        $matriculaIds = Matricula::where('curso_id', $curso->id)
            ->where('estado', EstadoMatricula::Activo)
            ->pluck('id')
            ->all();

        $filas = [];

        foreach ($data['estados'] as $matriculaId => $estado) {
            if (! in_array((int) $matriculaId, $matriculaIds, true)) {
                continue;
            }

            $filas[] = [
                'matricula_id' => (int) $matriculaId,
                'fecha' => $data['fecha'],
                'estado' => $estado,
                'registrado_por' => $request->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($filas !== []) {
            Asistencia::upsert($filas, ['matricula_id', 'fecha'], ['estado', 'registrado_por', 'updated_at']);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Asistencia guardada.')]);

        return redirect()->route('cursos.asistencia.show', ['curso' => $curso, 'fecha' => $data['fecha']]);
    }
}
