<?php

use App\Listeners\SendLikeNotification;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use App\Events\LikeCreated;
use function Pest\Laravel\post;
use function Pest\Laravel\actingAs;
use Illuminate\Support\Facades\Event;

use Illuminate\Database\Eloquent\Model;

beforeEach(function () {
    Event::fake();
    $this->user = User::factory()->create();
});

it('required authentication to like', function () {
    post(route('likes.store', ['post', 1]))
        ->assertRedirect(route('login'));
});

it('allow liking a likeable', function (Model $likeable) {
    actingAs($this->user)
        ->fromRoute('dashboard')
        ->post(route('likes.store', [$likeable->getMorphClass(), $likeable->id]))
        ->assertRedirect();

    $this->assertDatabaseHas(Like::class, [
        'user_id'       => $this->user->id,
        'likeable_id'   => $likeable->id,
        'likeable_type' => $likeable->getMorphClass(),
    ]);

    Event::assertDispatched(LikeCreated::class);
    Event::assertListening(
        LikeCreated::class,
        SendLikeNotification::class
    );
    expect($likeable->refresh()->likes_count)->toBe(1);
})
    ->with([
        fn () => Post::factory()->create(),
        fn () => Comment::factory()->create(),
    ]);

it('prevents liking something you already liked', function () {
    $like = Like::factory()->create();
    $likeable = $like->likeable;

    actingAs($like->user)
        ->post(route('likes.store', [$likeable->getMorphClass(), $likeable->id]))
        ->assertForbidden();
});

it('it only allow supported model', function () {
    actingAs($this->user)
        ->post(route('likes.store', [$this->user->getMorphClass(), $this->user->id]))
        ->assertForbidden();
});

it('throws a 404 if a type is unsupported', function () {
    actingAs($this->user)
        ->post(route('likes.store', ['foo', 1]))
        ->assertNotFound();
});

// testcase for unlike
it('required authentication to unlike', function () {
    post(route('likes.destroy', ['post', 1]))
        ->assertRedirect(route('login'));
});

it('allow unliking a likeable', function (Model $likeable) {
    Like::factory()->for($this->user)->for($likeable, 'likeable')->create();

    actingAs($this->user)
        ->fromRoute('dashboard')
        ->delete(route('likes.destroy', [$likeable->getMorphClass(), $likeable->id]))
        ->assertRedirect('dashboard');

    $this->assertDatabaseEmpty(Like::class);

    expect($likeable->refresh()->likes_count)->toBe(0);
})
    ->with([
        fn () => Post::factory()->create(['likes_count' => 1]),
        fn () => Comment::factory()->create(['likes_count' => 1]),
    ]);

it('prevents unliking something you havent liked', function () {
    $likeable = Post::factory()->create();

    actingAs($this->user)
        ->delete(route('likes.destroy', [$likeable->getMorphClass(), $likeable->id]))
        ->assertForbidden();
});

it('it only allow supported model when unlike', function () {
    $this->user = User::factory()->create();

    actingAs($this->user)
        ->delete(route('likes.destroy', [$this->user->getMorphClass(), $this->user->id]))
        ->assertForbidden();
});

it('throws a 404 if a type is unsupported when unlike', function () {
    actingAs($this->user)
        ->post(route('likes.store', ['foo', 1]))
        ->assertNotFound();
});
