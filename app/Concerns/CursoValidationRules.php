<?php

namespace App\Concerns;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait CursoValidationRules
{
    /**
     * Get the validation rules used to validate a curso.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function cursoRules(?int $cursoId = null): array
    {
        return [
            'ciclo_lectivo_id' => ['required', 'integer', 'exists:ciclos_lectivos,id'],
            'nivel' => ['required', Rule::in(['primaria', 'secundaria'])],
            'anio_grado' => [
                'required',
                'integer',
                'between:1,6',
                Rule::unique('cursos')
                    ->where(fn ($query) => $query
                        ->where('ciclo_lectivo_id', $this->input('ciclo_lectivo_id'))
                        ->where('nivel', $this->input('nivel'))
                        ->where('division', $this->input('division')))
                    ->ignore($cursoId),
            ],
            'division' => ['required', 'string', 'max:5'],
            'turno' => ['nullable', 'string', 'in:mañana,tarde,noche'],
        ];
    }
}
