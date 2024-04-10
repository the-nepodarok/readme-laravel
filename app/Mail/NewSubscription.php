<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class NewSubscription extends Mailable
{
    use Queueable, SerializesModels;

    public string $url; // ссылка на подписавшегося пользователя

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public User $subscriber
    )
    {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'У вас новый подписчик',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.new-subscription',
        );
    }

    public function bind(User $user, User $subscriber)
    {
        $this->user = $user;
        $this->subscriber = $subscriber;

        $this->url = url(route('user.view', $this->subscriber));
    }
}
