<?php

namespace App\Listeners;

use App\Events\LikeCreated;
use App\Notifications\LikeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendLikeNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(LikeCreated $event): void
    {
        $like = $event->like;
        $likeable = $like->likeable;

        // Get the author of the likeable (post or comment)
        $author = $likeable->user;

        // Don't send notification if user likes their own content
        if ($like->user_id === $author->id) {
            return;
        }

        // Send notification to the author
        $author->notify(new LikeNotification($like));
    }
}
