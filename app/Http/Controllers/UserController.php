<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewSubscriptionMessage;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function view(User $user, string $tab = 'posts', Post $post = null)
    {
        $authUser = Auth::user();

        // Добавление счётчиков
        $user->loadCount(['likes', 'posts', 'followers']);

        // Пост для показа комментариев
        $showCommentsPost = $post ?? new Post();

        // Проверка подписки на переданного пользователя
        $isFollowing = $authUser->isSubscribedTo($user);

        switch($tab) {
            case('posts'):
                // Получение постов пользователя
                $posts = $user->posts()
                    ->withCount('likes')
                    ->orderByDesc('created_at')->get();
                break;

            case('likes'):
                // Получение лайков
                $likes = new Collection();
                foreach ($user->posts as $post) {
                    $likes->push($post->likes);
                }

                // Сортировка по дате лайка
                $likes = $likes->flatten(1)->sortByDesc('created_at');
                break;
            case('subscriptions'):
                $followers = $user->followers()->withCount(['posts', 'followers'])->get();
        }

        return view('profile', [
            'user' => $user,
            'authUser' => $authUser,
            'isFollowing' => $isFollowing,
            'followers' => $followers ?? null,
            'posts' => $posts ?? null,
            'likes' => $likes ?? null,
            'tab' => $tab,
            'showCommentsPost' => $showCommentsPost,
        ]);
    }

    /**
     * Подписаться на пользователя
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function follow(User $user)
    {
        Auth::user()->subscribeTo($user);

        // Отправка уведомления о новом подписчике
        SendNewSubscriptionMessage::dispatch($user, Auth::user())
            ->delay(now()->addMinute());

        return redirect()->back();
    }

    /**
     * Отписаться от пользователя
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unfollow(User $user)
    {
        Auth::user()->unsubscribeFrom($user);

        return redirect()->back();
    }
}
