<?php

namespace App\Notifications;

use App\Models\Like;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class LikeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Like $like
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'message'        => $this->getMessage(),
            'like_id'        => $this->like->id,
            'user_id'        => $this->like->user_id,
            'user_name'      => $this->like->user->name,
            'likeable_type'  => $this->like->likeable_type,
            'likeable_id'    => $this->like->likeable_id,
            'likeable_title' => $this->getLikeableTitle(),
        ]);
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id'             => $this->id,
            'type'           => get_class($this),
            'message'        => $this->getMessage(),
            'like_id'        => $this->like->id,
            'user_id'        => $this->like->user_id,
            'user_name'      => $this->like->user->name,
            'likeable_type'  => $this->like->likeable_type,
            'likeable_id'    => $this->like->likeable_id,
            'likeable_title' => $this->getLikeableTitle(),
            'created_at'     => now(),
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message'        => $this->getMessage(),
            'like_id'        => $this->like->id,
            'user_id'        => $this->like->user_id,
            'user_name'      => $this->like->user->name,
            'likeable_type'  => $this->like->likeable_type,
            'likeable_id'    => $this->like->likeable_id,
            'likeable_title' => $this->getLikeableTitle(),
        ];
    }

    private function getMessage(): string
    {
        $userName = $this->like->user->name;
        $type = $this->like->likeable_type === 'post' ? 'post' : 'comment';

        return "{$userName} liked your {$type}";
    }

    private function getLikeableTitle(): string
    {
        if ($this->like->likeable_type === 'post') {
            return $this->like->likeable->title;
        }

        return substr($this->like->likeable->body, 0, 50).'...';
    }

    private function getLikeableUrl(): string
    {
        if ($this->like->likeable_type === 'App\\Models\\Post') {
            return $this->like->likeable->showRoute();
        }

        return $this->like->likeable->post->showRoute();
    }
}
