<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    /**
     * Roles that are assigned through their own CRUD (Profesor, Preceptor) and
     * must never be assignable from this generic usuario form.
     *
     * @var array<int, string>
     */
    public const ROLES_CON_CRUD_PROPIO = ['Profesor', 'Preceptor'];

    /**
     * Display a listing of the internal users (not linked to a Profesor, Alumno or Preceptor).
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();

        $usuarios = User::query()
            ->whereDoesntHave('profesor')
            ->whereDoesntHave('alumno')
            ->whereDoesntHave('preceptor')
            ->with('roles')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('usuarios/Index', [
            'usuarios' => $usuarios,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new usuario.
     */
    public function create(): Response
    {
        return Inertia::render('usuarios/Create', [
            'roles' => $this->rolesAsignables(),
        ]);
    }

    /**
     * Store a newly created usuario.
     */
    public function store(StoreUsuarioRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $usuario = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $usuario->assignRole($data['role']);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Usuario creado.')]);

        return to_route('usuarios.index');
    }

    /**
     * Show the form for editing a usuario.
     */
    public function edit(User $usuario): Response
    {
        $this->ensureManageable($usuario);

        return Inertia::render('usuarios/Edit', [
            'usuario' => $usuario->load('roles'),
            'roles' => $this->rolesAsignables(),
        ]);
    }

    /**
     * Update the specified usuario.
     */
    public function update(UpdateUsuarioRequest $request, User $usuario): RedirectResponse
    {
        $this->ensureManageable($usuario);

        $data = $request->validated();

        $usuario->update([
            'name' => $data['name'],
            'email' => $data['email'],
            ...(filled($data['password'] ?? null) ? ['password' => $data['password']] : []),
        ]);

        $usuario->syncRoles([$data['role']]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Usuario actualizado.')]);

        return to_route('usuarios.index');
    }

    /**
     * Remove the specified usuario.
     */
    public function destroy(Request $request, User $usuario): RedirectResponse
    {
        $this->ensureManageable($usuario);

        abort_if($usuario->id === $request->user()->id, 403, __('No podés eliminar tu propio usuario.'));

        $usuario->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Usuario eliminado.')]);

        return to_route('usuarios.index');
    }

    /**
     * Ensure the usuario is not managed elsewhere (Profesores/Alumnos have their own CRUD).
     */
    protected function ensureManageable(User $usuario): void
    {
        abort_if(
            $usuario->profesor()->exists() || $usuario->alumno()->exists() || $usuario->preceptor()->exists(),
            404
        );
    }

    /**
     * Roles that can be assigned from this generic usuario form, excluding those
     * with their own CRUD (Profesor, Preceptor), which also create a linked record.
     *
     * @return Collection<int, string>
     */
    protected function rolesAsignables(): Collection
    {
        return Role::whereNotIn('name', self::ROLES_CON_CRUD_PROPIO)
            ->orderBy('name')
            ->pluck('name');
    }
}
