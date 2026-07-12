<?php

namespace App\Http\Requests;

use App\Concerns\PreceptorValidationRules;
use App\Models\Preceptor;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read Preceptor $preceptor
 */
class UpdatePreceptorRequest extends FormRequest
{
    use PreceptorValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...$this->preceptorRules($this->preceptor->id),
            'activo' => ['boolean'],
        ];
    }
}
