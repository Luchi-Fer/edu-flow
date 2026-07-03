<?php

namespace App\Concerns;

use App\Models\Materia;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait MateriaValidationRules
{
    /**
     * Get the validation rules used to validate a materia.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function materiaRules(?int $materiaId = null): array
    {
        return [
            'nombre' => [
                'required',
                'string',
                'max:255',
                $materiaId === null
                    ? Rule::unique(Materia::class)
                    : Rule::unique(Materia::class)->ignore($materiaId),
            ],
            'descripcion' => ['nullable', 'string'],
        ];
    }
}
