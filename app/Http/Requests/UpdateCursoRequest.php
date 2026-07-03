<?php

namespace App\Http\Requests;

use App\Concerns\CursoValidationRules;
use App\Models\Curso;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read Curso $curso
 */
class UpdateCursoRequest extends FormRequest
{
    use CursoValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->cursoRules($this->curso->id);
    }
}
