<?php

use App\Models\Post;
use function Pest\Laravel\get;

use App\Http\Resources\PostResource;
use Inertia\Testing\AssertableInertia;

it('should return the correct component', function () {
    get(route('posts.index'))
        ->assertInertia(fn (AssertableInertia $assertableInertia) => $assertableInertia
            ->component('Posts/Index')
    );
});

// index page test
it('passes posts to the view', function() {
    $posts = Post::factory(3)->create();

    get(route('posts.index'))
        ->assertHasPaginatedResource('posts', PostResource::collection($posts->reverse()));
});
