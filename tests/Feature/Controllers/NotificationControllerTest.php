<?php

use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\actingAs;
use Illuminate\Support\Facades\Event;
use App\Notifications\LikeNotification;

use App\Events\NotificationMarkedAsRead;

beforeEach(function () {
    $this->user = User::factory()->create(['name' => fake()->name()]);
    $this->otherUser = User::factory()->create(['name' => fake()->name()]);
    Event::fake();
});

describe('index method', function () {
    it('requires authentication to access notifications index', function () {
        get(route('notifications.index'))
            ->assertRedirect(route('login'));
    });

    it('returns the correct component', function () {
        actingAs($this->user)
            ->get(route('notifications.index'))
            ->assertComponent('Notifications/Index');
    });

    it('passes notifications to the view', function () {
        // Create some test notifications using actual notification system
        $posts = Post::factory(5)->create();

        foreach ($posts as $index => $post) {
            $like = Like::factory()->create([
                'user_id' => $this->otherUser->id,
                'likeable_type' => Post::class,
                'likeable_id' => $post->id,
            ]);

            // Send notification to our test user
            $this->user->notify(new LikeNotification($like));
        }

        $response = actingAs($this->user)
            ->get(route('notifications.index'));

        // Verify that notifications prop exists and has the expected structure
        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Notifications/Index')
                ->has('notifications.data', 5)
                ->has('notifications.meta')
                ->has('notifications.links')
            );
    });

    it('paginates notifications with 10 items per page', function () {
        // Create 15 test notifications
        $posts = Post::factory(15)->create();

        foreach ($posts as $post) {
            $like = Like::factory()->create([
                'user_id' => $this->otherUser->id,
                'likeable_type' => Post::class,
                'likeable_id' => $post->id,
            ]);

            $this->user->notify(new LikeNotification($like));
        }

        $response = actingAs($this->user)
            ->get(route('notifications.index'));

        // Verify pagination structure
        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Notifications/Index')
                ->has('notifications.data', 10) // First page should have 10 items
                ->has('notifications.meta')
                ->where('notifications.meta.total', 15) // Total should be 15
                ->has('notifications.links')
            );
    });

    it('shows notifications in latest order', function () {
        // Create old notification
        $oldPost = Post::factory()->create();
        $oldLike = Like::factory()->create([
            'user_id' => $this->otherUser->id,
            'likeable_type' => Post::class,
            'likeable_id' => $oldPost->id,
            'created_at' => now()->subHours(2),
        ]);
        $this->user->notify(new LikeNotification($oldLike));

        // Wait a bit and create new notification
        sleep(1);
        $newPost = Post::factory()->create();
        $newLike = Like::factory()->create([
            'user_id' => $this->otherUser->id,
            'likeable_type' => Post::class,
            'likeable_id' => $newPost->id,
        ]);
        $this->user->notify(new LikeNotification($newLike));

        $response = actingAs($this->user)
            ->get(route('notifications.index'));

        // Check that notifications are in the latest order
        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Notifications/Index')
                ->has('notifications.data', 2)
                ->where('notifications.data.0.data.likeable_id', $newPost->id)
                ->where('notifications.data.1.data.likeable_id', $oldPost->id)
            );
    });
});

describe('markAsRead method', function () {
    it('requires authentication to mark notifications as read', function () {
        patch(route('api.notifications.read'))
            ->assertRedirect(route('login'));
    });

    it('marks a specific notification as read', function () {
        // Create a test notification using real notification system
        $post = Post::factory()->create();
        $like = Like::factory()->create([
            'user_id' => $this->otherUser->id,
            'likeable_type' => Post::class,
            'likeable_id' => $post->id,
        ]);

        $this->user->notify(new LikeNotification($like));
        $notification = $this->user->notifications()->first();

        expect($notification->read_at)->toBeNull();

        actingAs($this->user)
            ->patch(route('api.notifications.read'), [
                'mark_all' => false,
                'notification_id' => $notification->id,
            ]);

        $notification->refresh();
        Event::assertDispatched(NotificationMarkedAsRead::class);
        expect($notification->read_at)->not->toBeNull();
    });

    it('marks all notifications as read', function () {
        // Create multiple unread notifications
        for ($i = 0; $i < 3; $i++) {
            $post = Post::factory()->create();
            $like = Like::factory()->create([
                'user_id' => $this->otherUser->id,
                'likeable_type' => Post::class,
                'likeable_id' => $post->id,
            ]);
            $this->user->notify(new LikeNotification($like));
        }

        // Verify all are unread
        expect($this->user->unreadNotifications->count())->toBe(3);

        actingAs($this->user)
            ->patch(route('api.notifications.read'), [
                'mark_all' => true,
            ]);

        // Refresh user and check all notifications are read
        $this->user->refresh();
        Event::assertDispatched(NotificationMarkedAsRead::class);
        expect($this->user->unreadNotifications->count())->toBe(0);
    });

    it('does nothing if notification does not exist', function () {
        $nonExistentId = \Illuminate\Support\Str::uuid();

        $response = actingAs($this->user)
            ->patch(route('api.notifications.read'), [
                'mark_all' => false,
                'notification_id' => $nonExistentId,
            ]);

        // Should not throw error
        $response->assertSuccessful();

        // Should not broadcast event for non-existent notification
        Event::assertNotDispatched(NotificationMarkedAsRead::class);
    });

    it('does nothing if notification belongs to different user', function () {
        $anotherUser = User::factory()->create();

        $post = Post::factory()->create();
        $like = Like::factory()->create([
            'user_id' => $this->otherUser->id,
            'likeable_type' => Post::class,
            'likeable_id' => $post->id,
        ]);

        // Send notification to different user
        $anotherUser->notify(new LikeNotification($like));
        $notification = $anotherUser->notifications()->first();

        actingAs($this->user)
            ->patch(route('api.notifications.read'), [
                'mark_all' => false,
                'notification_id' => $notification->id,
            ]);

        // Notification should remain unread
        $notification->refresh();
        expect($notification->read_at)->toBeNull();

        // Should not broadcast event
        Event::assertNotDispatched(NotificationMarkedAsRead::class);
    });
});

describe('list method', function () {
    it('requires authentication to access notifications list API', function () {
        get(route('api.notifications.list'))
            ->assertRedirect(route('login'));
    });

    it('returns notifications with correct JSON structure', function () {
        // Create some test notifications
        for ($i = 0; $i < 3; $i++) {
            $post = Post::factory()->create();
            $like = Like::factory()->create([
                'user_id' => $this->otherUser->id,
                'likeable_type' => Post::class,
                'likeable_id' => $post->id,
            ]);

            $this->user->notify(new LikeNotification($like));

            // Mark first notification as read
            if ($i === 0) {
                $this->user->notifications()->first()->markAsRead();
            }
        }

        $response = actingAs($this->user)
            ->get(route('api.notifications.list'))
            ->assertSuccessful()
            ->assertJsonStructure([
                'notifications' => [
                    '*' => [
                        'id',
                        'type',
                        'data',
                        'read_at',
                        'created_at',
                        'updated_at',
                    ]
                ],
                'unreadCount'
            ]);

        $json = $response->json();
        expect($json['notifications'])->toHaveCount(3);
        expect($json['unreadCount'])->toBe(2); // Only 2 unread notifications
    });

    it('returns latest 10 notifications only', function () {
        // Create 15 notifications
        for ($i = 0; $i < 15; $i++) {
            $post = Post::factory()->create();
            $like = Like::factory()->create([
                'user_id' => $this->otherUser->id,
                'likeable_type' => Post::class,
                'likeable_id' => $post->id,
            ]);

            $this->user->notify(new LikeNotification($like));
        }

        $response = actingAs($this->user)
            ->get(route('api.notifications.list'))
            ->assertSuccessful();

        $json = $response->json();
        expect($json['notifications'])->toHaveCount(10);
    });

    it('returns notifications in latest order', function () {
        $oldPost = Post::factory()->create();
        $oldLike = Like::factory()->create([
            'user_id' => $this->otherUser->id,
            'likeable_type' => Post::class,
            'likeable_id' => $oldPost->id,
        ]);
        $this->user->notify(new LikeNotification($oldLike));

        // Wait to ensure different timestamps
        sleep(1);

        $newPost = Post::factory()->create();
        $newLike = Like::factory()->create([
            'user_id' => $this->otherUser->id,
            'likeable_type' => Post::class,
            'likeable_id' => $newPost->id,
        ]);
        $this->user->notify(new LikeNotification($newLike));

        $response = actingAs($this->user)
            ->get(route('api.notifications.list'))
            ->assertSuccessful();

        $json = $response->json();
        expect($json['notifications'][0]['data']['likeable_id'])->toBe($newPost->id);
        expect($json['notifications'][1]['data']['likeable_id'])->toBe($oldPost->id);
    });

    it('correctly counts unread notifications', function () {
        for ($i = 0; $i < 5; $i++) {
            $post = Post::factory()->create();
            $like = Like::factory()->create([
                'user_id' => $this->otherUser->id,
                'likeable_type' => Post::class,
                'likeable_id' => $post->id,
            ]);

            $this->user->notify(new LikeNotification($like));
        }

        // Mark first 2 as read
        $notifications = $this->user->notifications()->latest()->take(2)->get();
        foreach ($notifications as $notification) {
            $notification->markAsRead();
        }

        $response = actingAs($this->user)
            ->get(route('api.notifications.list'))
            ->assertSuccessful();

        $json = $response->json();
        expect($json['unreadCount'])->toBe(3); // 3 unread notifications
    });

    it('returns zero unread count when no notifications exist', function () {
        $response = actingAs($this->user)
            ->get(route('api.notifications.list'))
            ->assertSuccessful();

        $json = $response->json();
        expect($json['notifications'])->toHaveCount(0);
        expect($json['unreadCount'])->toBe(0);
    });

    it('only returns notifications for the authenticated user', function () {
        $anotherUser = User::factory()->create();

        // Create notification for current user
        $post1 = Post::factory()->create();
        $like1 = Like::factory()->create([
            'user_id' => $this->otherUser->id,
            'likeable_type' => Post::class,
            'likeable_id' => $post1->id,
        ]);
        $this->user->notify(new LikeNotification($like1));

        // Create notification for other user
        $post2 = Post::factory()->create();
        $like2 = Like::factory()->create([
            'user_id' => $this->otherUser->id,
            'likeable_type' => Post::class,
            'likeable_id' => $post2->id,
        ]);
        $anotherUser->notify(new LikeNotification($like2));

        $response = actingAs($this->user)
            ->get(route('api.notifications.list'))
            ->assertSuccessful();

        $json = $response->json();
        expect($json['notifications'])->toHaveCount(1);
        expect($json['notifications'][0]['data']['likeable_id'])->toBe($post1->id);
    });
});
