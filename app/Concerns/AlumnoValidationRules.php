<?php

namespace App\Concerns;

use App\Models\Alumno;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait AlumnoValidationRules
{
    /**
     * Get the validation rules used to validate an alumno.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function alumnoRules(?int $alumnoId = null): array
    {
        return [
            'dni' => [
                'required',
                'string',
                'max:15',
                $alumnoId === null
                    ? Rule::unique(Alumno::class)
                    : Rule::unique(Alumno::class)->ignore($alumnoId),
            ],
            'apellido' => ['required', 'string', 'max:255'],
            'nombre' => ['required', 'string', 'max:255'],
            'fecha_nacimiento' => ['required', 'date'],
            'genero' => ['nullable', 'in:M,F,X'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:255'],
            'nombre_tutor' => ['nullable', 'string', 'max:255'],
            'telefono_tutor' => ['nullable', 'string', 'max:255'],
            'email_tutor' => ['nullable', 'email', 'max:255'],
            'fecha_ingreso' => ['required', 'date'],
        ];
    }
}
