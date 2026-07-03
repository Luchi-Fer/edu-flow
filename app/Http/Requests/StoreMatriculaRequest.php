<?php

namespace App\Http\Requests;

use App\Models\Curso;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property-read Curso $curso
 */
class StoreMatriculaRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'alumno_id' => [
                'required',
                'integer',
                'exists:alumnos,id',
                Rule::unique('matriculas')
                    ->where(fn ($query) => $query->where('curso_id', $this->curso->id)),
            ],
            'fecha_matriculacion' => ['required', 'date'],
        ];
    }
}
