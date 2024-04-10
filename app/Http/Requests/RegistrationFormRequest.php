<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegistrationFormRequest extends FormRequest
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
          'name' => 'required|string|min:3',
          'email' => 'required|email|unique:users',
          'password' => 'required|confirmed',
          'avatar' => 'nullable|image|mimes:jpg,png,gif|max:10240'
        ];
    }

    public function messages():array
    {
        return [
            'name.required' => 'Введите имя пользователя',
            'name.min' => 'Имя слишком короткое',
            'email.unique' => 'Пользователь с такой почтой уже существует',
            'email.required' => 'Введите электронную почту',
            'password.required' => 'Введите пароль',
            'password.confirmed' => 'Пароли не совпадают',
            'avatar.mimes' => 'Неверный формат файла. Приложите картинку в формате jpg, png или gif',
            'avatar.max' => 'Файл слишком большой. Размер не должен превышать 10 мб',
        ];
    }
}
