<?php

namespace App\Concerns;

use App\Models\Preceptor;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait PreceptorValidationRules
{
    /**
     * Get the validation rules used to validate a preceptor.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function preceptorRules(?int $preceptorId = null): array
    {
        return [
            'dni' => [
                'required',
                'string',
                'max:15',
                $preceptorId === null
                    ? Rule::unique(Preceptor::class)
                    : Rule::unique(Preceptor::class)->ignore($preceptorId),
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
