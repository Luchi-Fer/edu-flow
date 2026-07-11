<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProfesorRequest;
use App\Http\Requests\UpdateProfesorRequest;
use App\Models\CursoMateria;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProfesorController extends Controller
{
    /**
     * Display a listing of profesores.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();

        $profesores = Profesor::query()
            ->with('user')
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

        return Inertia::render('profesores/Index', [
            'profesores' => $profesores,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new profesor.
     */
    public function create(): Response
    {
        return Inertia::render('profesores/Create');
    }

    /**
     * Store a newly created profesor along with its user account.
     */
    public function store(StoreProfesorRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => "{$data['nombre']} {$data['apellido']}",
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            $user->assignRole('Profesor');

            Profesor::create([
                ...collect($data)->except(['email', 'password'])->all(),
                'user_id' => $user->id,
            ]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profesor creado.')]);

        return to_route('profesores.index');
    }

    /**
     * Search activo profesores, for use in async comboboxes.
     */
    public function buscar(Request $request): JsonResponse
    {
        $search = $request->string('search')->trim()->toString();

        $profesores = Profesor::where('activo', true)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('nombre', 'like', "%{$search}%")
                        ->orWhere('apellido', 'like', "%{$search}%");
                });
            })
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->limit(20)
            ->get(['id', 'nombre', 'apellido']);

        return response()->json($profesores);
    }

    /**
     * Display the profesor's curso/materia assignments.
     */
    public function show(Profesor $profesor): Response
    {
        $asignaciones = CursoMateria::where('profesor_id', $profesor->id)
            ->with(['curso.cicloLectivo', 'materia'])
            ->get()
            ->sortBy(fn (CursoMateria $asignacion) => [
                $asignacion->curso->cicloLectivo->anio,
                $asignacion->curso->nivel->value,
                $asignacion->curso->anio_grado,
                $asignacion->curso->division,
            ])
            ->values();

        return Inertia::render('profesores/Show', [
            'profesor' => $profesor->load('user'),
            'asignaciones' => $asignaciones->map(fn (CursoMateria $asignacion) => [
                'id' => $asignacion->id,
                'ciclo_lectivo' => $asignacion->curso->cicloLectivo->anio,
                'curso' => $asignacion->curso->label,
                'materia' => $asignacion->materia->nombre,
            ]),
        ]);
    }

    /**
     * Show the form for editing a profesor.
     */
    public function edit(Profesor $profesor): Response
    {
        return Inertia::render('profesores/Edit', [
            'profesor' => $profesor->load('user'),
        ]);
    }

    /**
     * Update the specified profesor.
     */
    public function update(UpdateProfesorRequest $request, Profesor $profesor): RedirectResponse
    {
        $profesor->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profesor actualizado.')]);

        return to_route('profesores.index');
    }

    /**
     * Remove the specified profesor along with its user account.
     */
    public function destroy(Profesor $profesor): RedirectResponse
    {
        DB::transaction(function () use ($profesor) {
            $user = $profesor->user;
            $profesor->delete();
            $user->delete();
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profesor eliminado.')]);

        return to_route('profesores.index');
    }
}
