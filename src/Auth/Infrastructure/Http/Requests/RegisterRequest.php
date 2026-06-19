<?php

namespace Src\Auth\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                  => ['required', 'string', 'max:255'],
            'username'              => ['required', 'string', 'max:255', 'unique:users,username'],
            'email'                 => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                  => 'El nombre es obligatorio.',
            'username.required'              => 'El nombre de usuario es obligatorio.',
            'username.unique'                => 'Este nombre de usuario ya está en uso.',
            'email.required'                 => 'El correo electrónico es obligatorio.',
            'email.email'                    => 'Ingresa un correo electrónico válido.',
            'email.unique'                   => 'Este correo ya está registrado.',
            'password.required'              => 'La contraseña es obligatoria.',
            'password.min'                   => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'             => 'Las contraseñas no coinciden.',
            'password_confirmation.required' => 'Confirma tu contraseña.',
        ];
    }
}
