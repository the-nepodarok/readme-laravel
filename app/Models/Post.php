<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'header',
        'text',
        'quote',
        'quote_author',
        'photo',
        'video',
        'link',
        'link_preview',
        'is_repost',
        'original_post_id',
        'view_count',
        'user_id',
        'category_id'
    ];

    public $timestamps = ['created_at'];
    const UPDATED_AT = null; // отключение поля даты обновления

    /**
     * Сериализация дат и времени
     * (требуется для правильного применения часового пояса)
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->setTimezone(env('APP_TIMEZONE'));
    }

    // Массив полей формы новой публикации
    public static array $labels = [
        'header' => 'Заголовок',
        'hashtags' => 'Теги',
        'text' => 'Текст поста',
        'quote' => 'Текст цитаты',
        'quote_author' => 'Автор',
        'link' => 'Ссылка',
        'video' => 'Ссылка YouTube',
        'photo_url' => 'Ссылка из интернета',
        'photo_file' => 'Файл фото',
    ];

    /**
     * Тип публикации
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Автор публикации
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->BelongsTo(User::class);
    }

    /**
     * Пользователи, лайкнувшие публикацию
     * @return BelongsToMany
     */
    public function likeUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Лайки публикации
     * @return HasMany
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function hashtags():BelongsToMany
    {
        return $this->belongsToMany(Hashtag::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Проверка лайка на публикации
     * @return bool
     */
    public function isLiked(): bool
    {
        return $this->likeUsers()->where('user_id', Auth::id())->exists();
    }

    /**
     * Получить все репосты публикации
     * @return Collection
     */
    public function reposts(): Collection
    {
        return self::where(['original_post_id' => $this->id])->get();
    }

    /**
     * Получить оригинал репоста
     * @return self Оригинальный пост или сам пост
     */
    public function getOriginalPost(): self
    {
        $original_post = $this;

        if ($this->is_repost) {
            $originalPost = self::find($this->original_post_id);
        }

        return $originalPost;
    }

    /**
     * Получить коллекцию постов с переданным хэштегом
     * @param string $hashtag
     * @return mixed
     */
    public static function searchByHashtag(string $hashtag = '')
    {
        return self::whereHas('hashtags', function (Builder $query) use ($hashtag) {
            $query->where(['name' => $hashtag]);
        })->get();
    }

    /**
     * Найти посты по переданному тексту
     * @param Builder $query
     * @param string $searchQuery
     * @return Builder
     */
    public function scopeSearchByText(Builder $query, string $searchQuery): Builder
    {
        return $query->whereFullText('text', $searchQuery)
            ->orWhere('quote', 'like', '%'.$searchQuery.'%')
            ->orWhere('header', 'like', '%'.$searchQuery.'%');
    }

    /**
     * Выбрать посты по переданной категории
     * @param Builder $query
     * @param array|string $filter
     * @return Builder
     */
    public function scopeCategoryFilter(Builder $query, array|string $filter): Builder
    {
        return self::whereIn('category_id', $filter);
    }
}
