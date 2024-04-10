<?php

namespace App\Mail;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class NewPost extends Mailable
{
    use Queueable, SerializesModels;

    public string $url; // ссылка на пользователя, опубликовавшего новый пост

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Post $post,
        public User $user,
        public User $author
    )
    {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Новая публикация от пользователя ' . $this->author->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.new-post'
        );
    }

    public function bind(Post $post, User $user): void
    {
        $this->post = $post;
        $this->user = $user;
        $this->author = User::find($this->post->user->id);

        $this->url = url(route('user.view', $this->author));
    }
}
