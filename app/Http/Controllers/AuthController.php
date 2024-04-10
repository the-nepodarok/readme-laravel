<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginFormRequest;
use App\Http\Requests\RegistrationFormRequest;
use App\Models\Category;
use App\Models\User;
use App\Support\FileUploadHandler;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Аутентификация и авторизация пользователя
     * @param LoginFormRequest $request
     * @return \Illuminate\Http\RedirectResponse Редирект на страницу ленты
     */
    public function login(LoginFormRequest $request)
    {
        if (Auth::attempt($request->validated())) {
            $request->session()->regenerate();

            // Добавление списка категорий постов в сессию
            session()->put('categories', Category::all());

            return redirect()->intended('popular');
        }

        return back()->withErrors([
            'password' => 'Неверный пароль'
        ])->withInput();
    }

    /**
     * Деавторизация пользователя
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout()
    {
        Auth::logout();
        return redirect()->route('landing');
    }

    /**
     * Регистрация нового пользователя
     * @return \Illuminate\Contracts\View\View
     */
    public function registration() {

        // Получение названий полей
        $labels = User::$labels;

        return view('registration', [
           'labels' => $labels,
        ]);
    }

    /**
     * Сценарий обработки формы регистрации
     * @param RegistrationFormRequest $request
     * @param FileUploadHandler $photoUploader
     * @return \Illuminate\Http\RedirectResponse Редирект на страницу формы входа
     */
    public function register(RegistrationFormRequest $request, FileUploadHandler $photoUploader)
    {
        $formData = $request->validated();
        $formData['password'] = Hash::make($formData['password']);

        // Загрузка аватара
        if (!empty($formData['avatar'])) {
            $formData['avatar'] = $photoUploader->upload($formData['avatar'], env('APP_AVATAR_PATH'));
        }

        User::create($formData);

        return redirect()->route('login');
    }
}
