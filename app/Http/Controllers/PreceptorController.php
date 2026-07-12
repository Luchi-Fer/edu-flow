<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePreceptorRequest;
use App\Http\Requests\UpdatePreceptorRequest;
use App\Models\Preceptor;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PreceptorController extends Controller
{
    /**
     * Display a listing of preceptores.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();

        $preceptores = Preceptor::query()
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

        return Inertia::render('preceptores/Index', [
            'preceptores' => $preceptores,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new preceptor.
     */
    public function create(): Response
    {
        return Inertia::render('preceptores/Create');
    }

    /**
     * Store a newly created preceptor along with its user account.
     */
    public function store(StorePreceptorRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => "{$data['nombre']} {$data['apellido']}",
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            $user->assignRole('Preceptor');

            Preceptor::create([
                ...collect($data)->except(['email', 'password'])->all(),
                'user_id' => $user->id,
            ]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Preceptor creado.')]);

        return to_route('preceptores.index');
    }

    /**
     * Search activo preceptores, for use in async comboboxes.
     */
    public function buscar(Request $request): JsonResponse
    {
        $search = $request->string('search')->trim()->toString();

        $preceptores = Preceptor::where('activo', true)
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
     * Display the preceptor's details.
     */
    public function show(Preceptor $preceptor): Response
    {
        return Inertia::render('preceptores/Show', [
            'preceptor' => $preceptor->load('user'),
        ]);
    }

    /**
     * Show the form for editing a preceptor.
     */
    public function edit(Preceptor $preceptor): Response
    {
        return Inertia::render('preceptores/Edit', [
            'preceptor' => $preceptor->load('user'),
        ]);
    }

    /**
     * Update the specified preceptor.
     */
    public function update(UpdatePreceptorRequest $request, Preceptor $preceptor): RedirectResponse
    {
        $preceptor->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Preceptor actualizado.')]);

        return to_route('preceptores.index');
    }

    /**
     * Remove the specified preceptor along with its user account.
     */
    public function destroy(Preceptor $preceptor): RedirectResponse
    {
        DB::transaction(function () use ($preceptor) {
            $user = $preceptor->user;
            $preceptor->delete();
            $user->delete();
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Preceptor eliminado.')]);

        return to_route('preceptores.index');
    }
}
