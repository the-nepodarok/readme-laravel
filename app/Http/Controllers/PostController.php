<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewPostFormRequest;
use App\Jobs\SendNewPostMessage;
use App\Jobs\UploadLinkThumbnail;
use App\Models\Hashtag;
use App\Models\Post;
use App\Support\FileUploadHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Отображение страницы популярного
     * @param $sort
     * @param $filter
     * @return \Illuminate\Contracts\View\View
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function index($sort = 'views', $filter = null)
    {
        // обработка запроса фильтрации
        $filters = explode('+', $filter);

        if ($filter) {
            // применение множественных фильтров
            $filters = str_split($filter);
            $posts = Post::categoryFilter($filters);
        } else {
            $posts = Post::query(); // инициировать построение запроса
        }

        // обработка запроса сортировки
        switch ($sort) {
            case 'views':
                $orderBy = 'view_count';
                break;
            case 'date':
                $orderBy = 'created_at';
                break;
            case 'likes':
                $orderBy = 'likes_count';
                $posts = $posts->withCount('likes');
                break;
        }

        // получение отсортированного списка постов с пагинацией
        $posts = $posts->orderByDesc($orderBy)
            ->withCount(['likes', 'comments'])
            ->paginate(env('APP_PAGE_POST_LIMIT'));

        // получение списка категорий постов из сессии
        $categories = session()->get('categories');

        return view('popular', [
            'posts' => $posts,
            'categories' => $categories,
            'filter' => $filter,
            'filters' => $filters,
            'sort' => $sort,
        ]);
    }

    /**
     * Отображение страницы поста
     * @param Post $post
     * @param bool $showComments
     * @return \Illuminate\Contracts\View\View
     */
    public function view(Post $post, bool $showComments = false)
    {
        // аутентифицированный пользователь
        $user = Auth::user();

        // Обновление счётчика просмотров
        $post->update([
            'view_count' => $post->view_count + 1,
        ]);

        // Получение количества лайков и комментариев
        $post->loadCount(['likes', 'comments']);

        // Получение всех комментариев к посту
        $comments = $post->comments;

        if (!$showComments) {
            $comments = $comments->take(env('APP_COMMENT_LIMIT')); // Ограничение по количеству отображаемых комментариев
        }

        // Проверка подписки на автора поста
        $isFollowing = $user->isSubscribedTo($post->user);

        return view('post', [
            'user' => $user,
            'post' => $post,
            'isFollowing' => $isFollowing,
            'comments' => $comments,
            'showComments' => $showComments,
        ]);
    }

    /**
     * Отображение страницы ленты
     * @param $filter
     * @return \Illuminate\Contracts\View\View
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function feed($filter = null)
    {
        $user = Auth::user();

        // Получение постов подписок пользователя
        $subscriptions = $user->subscriptions()->pluck('users.id');
        $posts = Post::whereIn('user_id', $subscriptions)->withCount(['likes', 'comments']);

        // обработка запроса фильтрации
        $filters = explode('+', $filter);
        if ($filter) {
            // применение множественных фильтров
            $posts = $posts->whereIn('category_id', $filters);
        }

        $posts = $posts->get();

        return view('feed', [
            'filter' => $filter,
            'filters' => $filters,
            'posts' => $posts,
            'categories' => session()->get('categories') // получение списка категорий из сессии
        ]);
    }

    /**
     * Отображение страницы создания нового поста
     * @param string $type
     * @return \Illuminate\Contracts\View\View
     */
    public function add(string $type = 'text')
    {
        $categories = session('categories');

        // Название текущего типа для скрытого заголовка формы
        $current = $categories->firstWhere('value', $type)->name;

        // Названия полей формы создания поста
        $labels = Post::$labels;

        return view('create', [
            'type' => $type,
            'categories' => $categories,
            'current' => $current,
            'labels' => $labels
        ]);
    }

    /**
     * Сценарий обработки формы нового поста
     * @param NewPostFormRequest $request
     * @param FileUploadHandler $fileUploader
     * @param string $type Тип публикации
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function create(NewPostFormRequest $request, FileUploadHandler $fileUploader, string $type)
    {
        // Получение отвалидированных данных формы
        $formData = $request->validated();

        // Добавление ID пользователя и типа публикации
        $formData['user_id'] = Auth::id();
        $formData['category_id'] = session('categories')->firstWhere('value', $type)->id;

        // Добавление картинки
        if ($type === 'photo') {

            // Картинка по ссылке
            if (!empty($formData['photo_url'])) {
              // Загрузка файла по ссылке
                try {
                    $filename = $fileUploader->uploadFromUrl($formData['photo_url'], env('APP_UPLOAD_PATH'));
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                }

                // Картинка из файла
            } elseif ($file = $formData['photo_file']) {
                $filename = $file->hashName();
                // Запись файла на диск
              try {
                  $file->store(env('APP_UPLOAD_PATH'));
              } catch (\Exception $e) {
                  $error = 'Не удалось сохранить файл на сервер. Попробуйте снова позже.';
              }
            }

            // Прерывание выполнения сценария при ошибке записи файла
            if (isset($error)) {
                return redirect()->back()->withErrors(['photo_url' => $error])->withInput();
            }

            // Прикрепление имени файла к данным поста
            $formData['photo'] = $filename;

            // Обработка ссылки на видео
        } else if ($type === 'video') {
            // Получение ссылки
            $link = $formData['video'];

            // Получение id видео
            $link = Str::of($link);
            if ($link->contains('watch?')) {
              $youtubeId = $link->after('?v=')->before('&');
            } elseif ($link->contains('youtu.be')) {
              $youtubeId = $link->after('/be')->before('/');
            }

            // Запись ID ссылки на видео
            $formData['video'] = $youtubeId;
        }

        // Создание нового поста
        $post = Post::create($formData);

        // Постановка в очередь задания на загрузку превью страницы
        if ($type === 'link') {
            UploadLinkThumbnail::dispatch($post);
        }

        // Обработка хэштегов
        if (isset($formData['hashtags'])) {
            $hashtags = $formData['hashtags'];

            // Отсечение лишних пробелов, разбиение на слова и удаление пустых элементов
            $hashtags = Str::of($hashtags)->trim()->explode('#')->filter();

            foreach ($hashtags as $hashtag) {
                // Удаление лишних символов
                Str::of($hashtag)->trim(' #');
                $hashtag = Hashtag::firstOrCreate(['name' => $hashtag]);

                // Прикрепление тега к посту
                $post->hashtags()->attach($hashtag);
            }
        }

        // Рассылка сообщений о новой публикации
        foreach (Auth::user()->followers as $follower) {
            SendNewPostMessage::dispatch($post, $follower)
                ->delay(now()->addMinute());
        }

        return redirect(route('post', $post));
    }

    public function search(Request $request, string $hashtag = null)
    {
        // Получение поискового запроса из хэштега или из пользовательского ввода
        $searchQuery = $hashtag ?: $request->input('search');

        if (!$searchQuery) {
            return redirect()->route('popular');
        }

        $searchQuery = Str::of($searchQuery);

        // Поиск по хэштегу
        if ($hashtag or $searchQuery->startsWith('#')) {
            $hashtag = $searchQuery->trim('#');

            $searchResults = Post::searchByHashtag($hashtag);
        } else {
            // Поиск по тексту
            $searchResults = Post::searchByText($searchQuery)
                ->withCount(['likes', 'comments'])
                ->get();
        }

        if ($searchResults->isEmpty()) {
            $view = view('no-results', compact('searchQuery'));
        } else {
            $view = view('search', compact(['searchQuery', 'searchResults']));
        }

        return $view;
    }

    /**
     * Добавление/снятие лайка публикации
     * @param Post $post
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function like(Post $post)
    {
        $user = Auth::user();

        // Проверка уже существующего лайка
        if ($user->favoritePosts()->where('post_id', $post->id)->exists()) {
            $user->favoritePosts()->detach($post);
        } else {
            $user->favoritePosts()->attach($post);
        }

        return redirect()->back();
    }

    /**
     * Репост публикации
     * @param Post $post
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function repost(Post $post)
    {
        // Проверка, что пользователь не репостит собственный пост
        if ($post->user_id !== Auth::id()) {
            $repost = $post->replicate()->fill(['user_id' => Auth::id()]); // Создание копии поста
            $repost->is_repost = true;
            $repost->original_post_id = $post->id;
            $repost->save();
        }

        return redirect(route('user.view', Auth::user()));
    }
}
