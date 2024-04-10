<?php

namespace App\Http\Controllers;

use App\Http\Requests\MessageFormRequest;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Отображение страницы сообщений
     * @param int|null $userId
     * @return \Illuminate\Contracts\View\View
     */
    public function index(int $userId = null)
    {
        $authUser = Auth::user(); // текущий пользователь
        $newDialogue = false; // флаг открытия переписки с новым пользователем

        // проверка существования пользователя по переданному id
        $dialogueUser = User::find($userId);

        // Получение списка диалогов
        $dialogues = $authUser->getDialogues();

        // Сортировка диалогов по дате последнего сообщения
        foreach ($dialogues as $dialogue) {
            $dialogue->lastMessageDate = Message::whereBelongsTo($dialogue, 'sender')
                ->orWhereBelongsTo($dialogue, 'receiver')
                ->lastMessage()->created_at->timestamp;
        }
        $dialogues = $dialogues->sortByDesc('lastMessageDate');

        $messages = new Collection();

        if ($userId && $dialogueUser->isNot($authUser)) {
             // Получение всех сообщений с переданным пользователем
            $messages = Message::sentTo($userId)->receivedFrom($userId)->get();

            if ($messages->isNotEmpty()) {
                // Получение непрочитанных сообщений
                $unreadMessages = Message::receivedFrom($userId)->unread()->get();

                // Перевод статуса непрочитанных сообщений
                if ($unreadMessages->isNotEmpty()) {
                    $unreadMessages->map(function ($message) {
                        $message->markAsRead();
                    });
                }
            } else {
                // Проверка условий для начала переписки с пользователем
                if ($dialogueUser && $authUser->isSubscribedTo($dialogueUser)) {
                    $newDialogue = true;

                    // Добавление адресата в начало списка диалогов
                    $dialogues->prepend($dialogueUser);
                }
            }
        }

        return view('messages', [
            'userId' => $userId,
            'dialogues' => $dialogues,
            'authUser' => $authUser,
            'messages' => $messages,
            'newDialogue' => $newDialogue,
        ]);
    }

    /**
     * Отправка нового сообщения
     * @param MessageFormRequest $request
     * @param $receiverId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function send(MessageFormRequest $request, $receiverId)
    {
        $data = $request->validated();
        $data['sender_id'] = Auth::id();
        $data['receiver_id'] = $receiverId;
        Message::create($data);

        return redirect()->back();
    }
}
