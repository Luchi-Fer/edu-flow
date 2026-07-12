<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCursoPreceptorRequest;
use App\Models\Curso;
use App\Models\Preceptor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CursoPreceptorController extends Controller
{
    /**
     * Search activo preceptores not yet assigned to the curso, for use in async comboboxes.
     */
    public function disponibles(Request $request, Curso $curso): JsonResponse
    {
        $search = $request->string('search')->trim()->toString();

        $preceptores = Preceptor::where('activo', true)
            ->whereNotIn('id', $curso->preceptores()->pluck('preceptores.id'))
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

        return response()->json($preceptores);
    }

    /**
     * Assign a preceptor to the curso.
     */
    public function store(StoreCursoPreceptorRequest $request, Curso $curso): RedirectResponse
    {
        $curso->preceptores()->attach($request->validated('preceptor_id'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Preceptor asignado.')]);

        return to_route('cursos.edit', $curso);
    }

    /**
     * Remove a preceptor assignment from the curso.
     */
    public function destroy(Curso $curso, Preceptor $preceptor): RedirectResponse
    {
        $curso->preceptores()->detach($preceptor->id);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Asignación eliminada.')]);

        return to_route('cursos.edit', $curso);
    }
}
