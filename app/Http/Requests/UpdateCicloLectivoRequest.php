<?php

namespace App\Http\Requests;

use App\Concerns\CicloLectivoValidationRules;
use App\Models\CicloLectivo;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read CicloLectivo $ciclo_lectivo
 */
class UpdateCicloLectivoRequest extends FormRequest
{
    use CicloLectivoValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...$this->cicloLectivoRules($this->ciclo_lectivo->id),
            'activo' => ['boolean'],
        ];
    }
}
