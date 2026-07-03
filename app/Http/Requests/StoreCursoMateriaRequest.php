<?php

namespace App\Http\Requests;

use App\Models\Curso;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property-read Curso $curso
 */
class StoreCursoMateriaRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'materia_id' => [
                'required',
                'integer',
                'exists:materias,id',
                Rule::unique('curso_materia')
                    ->where(fn ($query) => $query->where('curso_id', $this->curso->id)),
            ],
            'profesor_id' => ['nullable', 'integer', 'exists:profesores,id'],
        ];
    }
}
