<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware('guest')->group(function () {
    // Страница лэндинга
    Route::view('/', 'landing')->name('landing');

    // Страница входа
    Route::view('/login', 'login')->name('login');

    // Сценарий обработки формы авторизации
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    // Страница регистрации
    Route::get('/registration', [AuthController::class, 'registration'])->name('registration');

    // Сценарий обработки формы регистрации
    Route::post('/register', [AuthController::class, 'register'])->name('register');
});

Route::middleware('auth')->group(function () {
    // Страница популярного
    Route::get('/popular/{sort?}/{filter?}', [PostController::class, 'index'])->where('filter', '^[0-9?+]+$')->name('popular');

    // Страница просмотра публикации
    Route::get('/post/{post}/{comments?}', [PostController::class, 'view'])->name('post');

    // Поставить/убрать лайк публикации
    Route::get('/like/{post}', [PostController::class, 'like'])->name('post.like');

    // Сделать репост публикации
    Route::get('/repost/{post}', [PostController::class, 'repost'])->name('post.repost');

    // Добавить комментарий к посту
    Route::post('/post/comment', [CommentController::class, 'create'])->name('add.comment');

    // Страница ленты
    Route::get('/feed/{filter?}', [PostController::class, 'feed'])->where('filter', '^[0-9?+]+$')->name('feed');

    // Страница сообщений
    Route::get('/messages/{user_id?}', [MessageController::class, 'index'])->whereNumber('user_id')->name('messages');

    // Отправка сообщения
    Route::post('/messages/{user}', [MessageController::class, 'send'])->name('message.send');

    // Страница добавления публикации
    Route::get('/create/{type?}', [PostController::class, 'add'])->name('post.add');

    // Сценарий обработки формы
    Route::post('/create/{type}', [PostController::class, 'create'])->name('post.create');

    // Страница поиска
    Route::get('/search/{hashtag:name?}', [PostController::class, 'search'])->name('search');

    // Подписаться на пользователя
    Route::get('/user/{user}/follow', [UserController::class, 'follow'])->name('user.follow');

    // Отписаться от пользователя
    Route::get('/user/{user}/unfollow', [UserController::class, 'unfollow'])->name('user.unfollow');

    // Страница профиля пользователя
    Route::get('/user/{user}/{tab?}/{post?}', [UserController::class, 'view'])->name('user.view');

    // Деавторизация
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::fallback(function () {
        return view('errors/404');
    });
    }
);
