<?php

namespace App\Jobs;

use App\Mail\NewPost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendNewPostMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private Post $post, private User $recepient)
    {}

    /**
     * Execute the job.
     */
    public function handle(NewPost $message): void
    {
        $message->bind($this->post, $this->recepient);

        Mail::to($this->recepient->email)->send($message);
    }
}
