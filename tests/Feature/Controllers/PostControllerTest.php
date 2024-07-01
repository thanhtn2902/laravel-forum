<?php

use App\Models\Post;
use function Pest\Laravel\get;

use App\Http\Resources\PostResource;

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
