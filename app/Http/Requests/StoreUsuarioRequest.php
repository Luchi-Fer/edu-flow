<?php

namespace App\Http\Requests;

use App\Concerns\PasswordValidationRules;
use App\Http\Controllers\UsuarioController;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class StoreUsuarioRequest extends FormRequest
{
    use PasswordValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'password' => $this->passwordRules(),
            'role' => [
                'required',
                'string',
                Rule::in(Role::whereNotIn('name', UsuarioController::ROLES_CON_CRUD_PROPIO)->pluck('name')),
            ],
        ];
    }
}
