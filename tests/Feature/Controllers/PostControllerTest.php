<?php

use App\Models\Post;
use App\Models\User;
use App\Models\Topic;

use App\Models\Comment;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

use function Pest\Laravel\actingAs;
use App\Http\Resources\PostResource;
use App\Http\Resources\CommentResource;
use App\Http\Resources\TopicResource;

beforeEach(function() {
    $this->validateData = [
        'title' => 'Hello World',
        'body' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quidem doloremque inventore nesciunt, vero accusantium illum hic sequi deserunt rerum aliquid, numquam, nam quis. Fuga cumque, similique, perferendis esse quibusdam saepe neque, sint nobis voluptate veritatis nostrum optio ipsam minus rem totam delectus animi excepturi dolor repudiandae! Illum ratione deserunt soluta doloremque aliquam, repellendus voluptates rerum in adipisci sapiente officiis nobis nam animi? Iste voluptates nemo sequi beatae sint debitis quod quidem sed soluta omnis mollitia fugit adipisci officiis accusantium temporibus, minima, repellat impedit. Inventore reiciendis iste, earum ratione molestiae explicabo saepe rem? Aperiam aut consectetur minus vero amet reprehenderit suscipit?'
    ];
});

it('should return the correct component', function () {
    get(route('posts.index'))
        ->assertComponent('Posts/Index');
});

// index page test
it('passes posts to the view', function() {
    $posts = Post::factory(3)->create();

    $posts->load(['user', 'topic']);

    get(route('posts.index'))
        ->assertHasPaginatedResource('posts', PostResource::collection($posts->reverse()));
});

// show page test
it('can show a post', function () {
    $post = Post::factory()->create();

    $post->load(['user', 'topic']);

    get($post->showRoute())
        ->assertComponent('Posts/Show')
        ->assertHasResource('post', PostResource::make($post));
});

it('pass comment to the view', function () {
    $post = Post::factory()->create();
    $comments = Comment::factory(2)->for($post)->create();

    $comments->load(['user', 'topic']);
    get($post->showRoute())
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
        ->assertRedirect(Post::latest('id')->first()->showRoute());
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

it('can filter to a topic', function() {
    $general = Topic::factory()->create();
    $posts = Post::factory(3)->for($general)->create();
    $otherPosts = Post::factory(3)->create();

    $posts->load(['user', 'topic']);

    get(route('posts.index', ['topic' => $general]))
        ->assertHasPaginatedResource('posts', PostResource::collection($posts->reverse()));
});

it('passes the selected topic to the view', function() {
    $topic = Topic::factory()->create();

    get(route('posts.index', ['topic' => $topic]))
        ->assertHasResource('selectedTopic', TopicResource::make($topic));
});
