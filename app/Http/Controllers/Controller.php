<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

abstract class Controller
{
    /**
     * Flash a friendly error toast and redirect back, for actions blocked
     * by dependent data (e.g. deleting a curso that still has alumnos).
     */
    protected function denegarEliminacion(string $mensaje): RedirectResponse
    {
        Inertia::flash('toast', ['type' => 'error', 'message' => $mensaje]);

        return back();
    }
}
