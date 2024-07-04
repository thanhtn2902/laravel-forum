<?php

use App\Models\Post;
use Illuminate\Support\Str;

use function Pest\Laravel\get;

beforeEach(function() {
    $this->initialPost = Post::factory()->create();
});

it('uses title case for titles', function() {
    $post = Post::factory()->create(['title' => 'Hello, how are you?']);

    expect($post->title)->toBe('Hello, How Are You?');
});

it('can generate a route to the show page', function() {
    expect($this->initialPost->showRoute())
        ->toBe(route('posts.show', [$this->initialPost, Str::slug($this->initialPost->title)]));
});

it('can generate additional query parameter on the show route', function() {
    expect($this->initialPost->showRoute(['page' => 2]))
        ->toBe(route('posts.show', [$this->initialPost, Str::slug($this->initialPost->title), 'page' => 2]));
});

it('will redirect if the slug is incorrect', function() {
    get(route('posts.show', [$this->initialPost, 'foo-bar']))
        ->assertRedirect($this->initialPost->showRoute());
});
