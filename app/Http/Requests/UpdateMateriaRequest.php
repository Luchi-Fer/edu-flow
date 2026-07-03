<?php

namespace App\Http\Requests;

use App\Concerns\MateriaValidationRules;
use App\Models\Materia;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read Materia $materia
 */
class UpdateMateriaRequest extends FormRequest
{
    use MateriaValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->materiaRules($this->materia->id);
    }
}
