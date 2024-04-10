<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    const CREATED_AT = 'registration_date';
    const UPDATED_AT = null; // отключение поля даты обновления

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar'
    ];

    /**
     * Сериализация дат и времени
     * (требуется для правильного применения часового пояса)
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return Carbon::instance($date)->setTimezone(env('APP_TIMEZONE'))->toISOString(true);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    // Массив полей формы регистрации
    public static array $labels = [
        'name' => 'Имя пользователя',
        'email' => 'Электронная почта',
        'password' => 'Пароль',
        'password_confirmation' => 'Повтор пароля',
        'avatar' => 'Аватар пользователя'
    ];

    public function posts():HasMany
    {
        return $this->HasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function favoritePosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'followers',
            'subscription_id',
            'follower_id'
        );
    }

    public function subscriptions(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'followers',
            'follower_id',
            'subscription_id'
        );
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /**
     * Подписаться на пользователя
     * @param User $user
     * @return void
     */
    public function subscribeTo(self $user): void
    {
        // Проверка уже существующей подписки и запрет подписки на самого себя
        if (!$this->isSubscribedTo($user) && $user->isNot($this)) {
            $user->followers()->attach($this);
        }
    }

    /**
     * Отписаться от пользователя
     * @param User $user
     * @return void
     */
    public function unsubscribeFrom(self $user): void
    {
        // Проверка существования подписки
        if ($this->isSubscribedTo($user)) {
            $user->followers()->detach($this);
        }
    }

    /**
     * Проверка подписки на переданного пользователя
     * @param User $user
     * @return bool
     */
    public function isSubscribedTo(self $user): bool
    {
        return $this->subscriptions()->where(['subscription_id' => $user->id])->exists();
    }

    /**
     * Получение списка пользователей, с которыми существует переписка
     * @return mixed
     */
    public function getDialogues(): mixed
    {
        return self::has('sentMessages')
            ->orHas('receivedMessages')
            ->get()->except($this->id);
    }
}
