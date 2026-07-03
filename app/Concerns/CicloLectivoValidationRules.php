<?php

namespace App\Concerns;

use App\Models\CicloLectivo;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait CicloLectivoValidationRules
{
    /**
     * Get the validation rules used to validate a ciclo lectivo.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function cicloLectivoRules(?int $cicloLectivoId = null): array
    {
        return [
            'anio' => [
                'required',
                'integer',
                'between:2000,2100',
                $cicloLectivoId === null
                    ? Rule::unique(CicloLectivo::class)
                    : Rule::unique(CicloLectivo::class)->ignore($cicloLectivoId),
            ],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after:fecha_inicio'],
        ];
    }
}
