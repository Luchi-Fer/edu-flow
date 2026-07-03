<?php

namespace App\Http\Requests;

use App\Concerns\CicloLectivoValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCicloLectivoRequest extends FormRequest
{
    use CicloLectivoValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->cicloLectivoRules();
    }
}
