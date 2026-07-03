<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCicloLectivoRequest;
use App\Http\Requests\UpdateCicloLectivoRequest;
use App\Models\CicloLectivo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CicloLectivoController extends Controller
{
    /**
     * Display a listing of ciclos lectivos.
     */
    public function index(): Response
    {
        $ciclosLectivos = CicloLectivo::query()
            ->orderByDesc('anio')
            ->paginate(15);

        return Inertia::render('ciclos-lectivos/Index', [
            'ciclosLectivos' => $ciclosLectivos,
        ]);
    }

    /**
     * Show the form for creating a new ciclo lectivo.
     */
    public function create(): Response
    {
        return Inertia::render('ciclos-lectivos/Create');
    }

    /**
     * Store a newly created ciclo lectivo.
     */
    public function store(StoreCicloLectivoRequest $request): RedirectResponse
    {
        CicloLectivo::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Ciclo lectivo creado.')]);

        return to_route('ciclos-lectivos.index');
    }

    /**
     * Show the form for editing a ciclo lectivo.
     */
    public function edit(CicloLectivo $ciclo_lectivo): Response
    {
        return Inertia::render('ciclos-lectivos/Edit', [
            'cicloLectivo' => $ciclo_lectivo,
        ]);
    }

    /**
     * Update the specified ciclo lectivo.
     *
     * Activating a ciclo lectivo deactivates every other one, since only
     * one can be active at a time.
     */
    public function update(UpdateCicloLectivoRequest $request, CicloLectivo $ciclo_lectivo): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $ciclo_lectivo) {
            if ($data['activo'] ?? false) {
                CicloLectivo::query()->where('id', '!=', $ciclo_lectivo->id)->update(['activo' => false]);
            }

            $ciclo_lectivo->update($data);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Ciclo lectivo actualizado.')]);

        return to_route('ciclos-lectivos.index');
    }

    /**
     * Remove the specified ciclo lectivo.
     */
    public function destroy(CicloLectivo $ciclo_lectivo): RedirectResponse
    {
        $ciclo_lectivo->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Ciclo lectivo eliminado.')]);

        return to_route('ciclos-lectivos.index');
    }
}
