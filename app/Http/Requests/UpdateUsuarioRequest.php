<?php

namespace App\Http\Requests;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

/**
 * @property-read User $usuario
 */
class UpdateUsuarioRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string|Closure>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($this->usuario->id)],
            'password' => ['nullable', 'string', Password::default(), 'confirmed'],
            'role' => [
                'required',
                'string',
                Rule::in(Role::pluck('name')),
                function (string $attribute, mixed $value, Closure $fail): void {
                    $esUnoMismo = $this->usuario->id === $this->user()->id;
                    $rolActual = $this->usuario->getRoleNames()->first();

                    if ($esUnoMismo && $value !== $rolActual) {
                        $fail(__('No podés cambiar tu propio rol.'));
                    }
                },
            ],
        ];
    }
}
