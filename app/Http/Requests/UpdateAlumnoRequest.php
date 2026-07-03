<?php

namespace App\Http\Requests;

use App\Concerns\AlumnoValidationRules;
use App\Models\Alumno;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read Alumno $alumno
 */
class UpdateAlumnoRequest extends FormRequest
{
    use AlumnoValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...$this->alumnoRules($this->alumno->id),
            'activo' => ['boolean'],
        ];
    }
}
