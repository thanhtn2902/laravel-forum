<?php

use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

beforeEach(function () {
    Event::fake();
});

it('required authentication to like', function () {
    post(route('likes.store', ['post', 1]))
        ->assertRedirect(route('login'));
});

it('allow liking a likeable', function (Model $likeable) {
    $user = User::factory()->create();

    actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('likes.store', [$likeable->getMorphClass(), $likeable->id]))
        ->assertRedirect();

    $this->assertDatabaseHas(Like::class, [
        'user_id'       => $user->id,
        'likeable_id'   => $likeable->id,
        'likeable_type' => $likeable->getMorphClass(),
    ]);

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
    $user = User::factory()->create();

    actingAs($user)
        ->post(route('likes.store', [$user->getMorphClass(), $user->id]))
        ->assertForbidden();
});

it('throws a 404 if a type is unsupported', function () {
    actingAs(User::factory()->create())
        ->post(route('likes.store', ['foo', 1]))
        ->assertNotFound();
});

// testcase for unlike
it('required authentication to unlike', function () {
    post(route('likes.destroy', ['post', 1]))
        ->assertRedirect(route('login'));
});

it('allow unliking a likeable', function (Model $likeable) {
    $user = User::factory()->create();
    Like::factory()->for($user)->for($likeable, 'likeable')->create();

    actingAs($user)
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

    actingAs(User::factory()->create())
        ->delete(route('likes.destroy', [$likeable->getMorphClass(), $likeable->id]))
        ->assertForbidden();
});

it('it only allow supported model when unlike', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->delete(route('likes.destroy', [$user->getMorphClass(), $user->id]))
        ->assertForbidden();
});

it('throws a 404 if a type is unsupported when unlike', function () {
    actingAs(User::factory()->create())
        ->post(route('likes.store', ['foo', 1]))
        ->assertNotFound();
});
