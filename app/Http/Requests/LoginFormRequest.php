<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class LoginFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'exists:users,email'
            ],
            'password' => [
                'required'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Введите логин',
            'email.email' => 'Введите валидный e-mail',
            'email.exists' => 'Пользователь с таким e-mail не найден',
            'password.required' => 'Введите пароль',
        ];
    }
}
