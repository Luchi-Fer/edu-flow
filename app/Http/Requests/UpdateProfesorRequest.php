<?php

namespace App\Http\Requests;

use App\Concerns\ProfesorValidationRules;
use App\Models\Profesor;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read Profesor $profesor
 */
class UpdateProfesorRequest extends FormRequest
{
    use ProfesorValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...$this->profesorRules($this->profesor->id),
            'activo' => ['boolean'],
        ];
    }
}
