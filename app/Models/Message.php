<?php

namespace App\Models;

use App\Scopes\AuthUserMessagesScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Message extends Model
{
    use HasFactory;

    const UPDATED_AT = null; // отключение поля даты обновления

    protected $fillable = [
        'text',
        'sender_id',
        'receiver_id',
        'is_read'
    ];

    /**
     * Подключение глобального скоупа: выборка сообщений по авторизованному пользователю
     * @return void
     */
    protected static function booted(): void
    {
        parent::booted();
        static::addGlobalScope(new AuthUserMessagesScope());
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Пометить сообщение прочитанным
     * @return bool
     */
    public function markAsRead(): bool
    {
        return $this->update(['is_read' => 1]);
    }

    /**
     * Проверка существования сообщений с переданным пользователем
     * @param int $userId
     * @return bool
     */
    public static function dialogueIsEmpty(int $userId): bool
    {
        return self::where(['sender_id' => $userId])
            ->orWhere(['receiver_id' => $userId])
            ->get()->isEmpty();
    }

    /**
     * Создание пустого сообщения с переданным пользователем при начале нового диалога
     * @param int $userId
     * @return Message
     */
    public static function startNewDialogue(int $userId)
    {
        $message = new self();
        return $message->fill([
            'text' => '',
            'is_read' => 1,
            'sender_id' => Auth::id(),
            'receiver_id' => $userId
        ]);
    }

    /**
     * Выбрать все полученные сообщения
     * @param Builder $query
     * @return Builder
     */
    public function scopeInboxMessages(Builder $query): Builder
    {
        return $query->where(['receiver_id' => Auth::id()]);
    }

    /**
     * Выбрать сообщения пользователя, отправленные переданному собеседнику
     * @param Builder $query
     * @param int $id
     * @return mixed Объект сообщения
     */
    public function scopeSentTo(Builder $query, int $userId): Builder
    {
        return $query->orWhere(['receiver_id' => $userId]);
    }

    /**
     * Выбрать сообщения пользователя, полученные от переданного собеседника
     * @param Builder $query
     * @param int $userId
     * @return Builder
     */
    public function scopeReceivedFrom(Builder $query, int $userId): Builder
    {
        return $query->orWhere(['sender_id' => $userId]);
    }

    /**
     * Выбрать непрочитанные сообщения
     * @param Builder $query
     * @return Builder
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    /**
     * Получить последнее сообщение из переписки
     * @param Builder $query
     * @param int $id
     * @return mixed
     */
    public function scopeLastMessage(Builder $query): Model
    {
        return $query->orderByDesc('messages.created_at')->first();
    }
}
