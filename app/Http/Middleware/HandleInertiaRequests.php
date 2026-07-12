<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'can' => [
                'gestionar-usuarios' => $request->user()?->can('gestionar-usuarios') ?? false,
                'gestionar-alumnos' => $request->user()?->can('gestionar-alumnos') ?? false,
                'gestionar-profesores' => $request->user()?->can('gestionar-profesores') ?? false,
                'gestionar-preceptores' => $request->user()?->can('gestionar-preceptores') ?? false,
                'gestionar-materias' => $request->user()?->can('gestionar-materias') ?? false,
                'gestionar-cursos' => $request->user()?->can('gestionar-cursos') ?? false,
                'tomar-asistencia' => $request->user()?->can('tomar-asistencia') ?? false,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
