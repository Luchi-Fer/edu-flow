<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProfesorRequest;
use App\Http\Requests\UpdateProfesorRequest;
use App\Models\Profesor;
use App\Models\User;
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

            Profesor::create([
                ...collect($data)->except(['email', 'password'])->all(),
                'user_id' => $user->id,
            ]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profesor creado.')]);

        return to_route('profesores.index');
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
