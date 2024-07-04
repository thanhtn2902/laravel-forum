<?php

use App\Models\Post;
use App\Models\User;
use App\Models\Comment;

use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\actingAs;

use App\Http\Resources\PostResource;
use App\Http\Resources\CommentResource;

beforeEach(function() {
    $this->validateData = [
        'title' => 'Hello World',
        'body' => 'This is my first post'
    ];
});

it('should return the correct component', function () {
    get(route('posts.index'))
        ->assertComponent('Posts/Index');
});

// index page test
it('passes posts to the view', function() {
    $posts = Post::factory(3)->create();

    $posts->load('user');

    get(route('posts.index'))
        ->assertHasPaginatedResource('posts', PostResource::collection($posts->reverse()));
});

// show page test
it('can show a post', function () {
    $post = Post::factory()->create();

    $post->load('user');

    get(route('posts.show', $post))
        ->assertComponent('Posts/Show')
        ->assertHasResource('post', PostResource::make($post));
});

it('pass comment to the view', function () {
    $post = Post::factory()->create();
    $comments = Comment::factory(2)->for($post)->create();

    $comments->load('user');
    get(route('posts.show', $post))
        ->assertHasPaginatedResource('comments', CommentResource::collection($comments->reverse()));
});

it('required authentication to create a post', function() {
    post(route('posts.store'))->assertRedirect(route('login'));
});

it('store a post', function() {
    $user = User::factory()->create();

    actingAs($user)
        ->post(route('posts.store'), $this->validateData);

    $this->assertDatabaseHas(Post::class, [
        ...$this->validateData,
        'user_id' => $user->id
    ]);
});

it('redirect to the post page', function() {
    $user = User::factory()->create();

    actingAs($user)
        ->post(route('posts.store'), $this->validateData)
        ->assertRedirect(route('posts.show', Post::latest('id')->first()));
});


it('required a valid data when create post', function($data, array|string $error) {
    actingAs(User::factory()->create())
        ->post(route('posts.store'), [...$this->validateData, ...$data])
        ->assertInvalid($error);
})
->with([
    [['title' => null], 'title'],
    [['title' => true], 'title'],
    [['title' => 1], 'title'],
    [['title' => 1.5], 'title'],
    [['title' => str_repeat('a', 121)], 'title'],
    [['title' => str_repeat('a', 9)], 'title'],
    [['body' => null], 'body'],
    [['body' => true], 'body'],
    [['body' => 1], 'body'],
    [['body' => 1.5], 'body'],
    [['body' => str_repeat('a', 10001)], 'body'],
    [['body' => str_repeat('a', 99)], 'body'],
]);

it('required authentication to access create post page', function() {
    get(route('posts.create'))->assertRedirect(route('login'));
});

it('returns the correct component', function() {
    actingAs(User::factory()->create())
        ->get(route('posts.create'))
        ->assertComponent('Posts/Create');
});