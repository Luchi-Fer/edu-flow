<?php

namespace App\Concerns;

use App\Models\Profesor;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait ProfesorValidationRules
{
    /**
     * Get the validation rules used to validate a profesor.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function profesorRules(?int $profesorId = null): array
    {
        return [
            'dni' => [
                'required',
                'string',
                'max:15',
                $profesorId === null
                    ? Rule::unique(Profesor::class)
                    : Rule::unique(Profesor::class)->ignore($profesorId),
            ],
            'apellido' => ['required', 'string', 'max:255'],
            'nombre' => ['required', 'string', 'max:255'],
            'fecha_nacimiento' => ['required', 'date'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:255'],
            'fecha_ingreso' => ['required', 'date'],
        ];
    }
}
