<?php

namespace App\Jobs;

use App\Mail\NewSubscription;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class SendNewSubscriptionMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private User $recepient, private User $sender)
    {}

    /**
     * Execute the job.
     */
    public function handle(NewSubscription $message): void
    {
        $message->bind($this->recepient, $this->sender);

        Mail::to($this->recepient->email)->send($message);
    }
}
