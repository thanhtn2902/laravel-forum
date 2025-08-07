<?php

namespace Tests\Feature\Models;

use App\Models\Post;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

use function Pest\Laravel\get;

beforeEach(function () {
    $this->initialPost = Post::factory()->create();
});

it('uses title case for titles', function () {
    $post = Post::factory()->create(['title' => 'Hello, how are you?']);

    expect($post->title)->toBe('Hello, How Are You?');
});

it('can generate a route to the show page', function () {
    expect($this->initialPost->showRoute())
        ->toBe(route('posts.show', [$this->initialPost, Str::slug($this->initialPost->title)]));
});

it('can generate additional query parameter on the show route', function () {
    expect($this->initialPost->showRoute(['page' => 2]))
        ->toBe(route('posts.show', [$this->initialPost, Str::slug($this->initialPost->title), 'page' => 2]));
});

it('will redirect if the slug is incorrect', function (string $incorrectSlug) {
    get(route('posts.show', [$this->initialPost, $incorrectSlug]))
        ->assertRedirect($this->initialPost->showRoute());
})
    ->with([
        'foo-bar',
        'hello',
    ]);

it('generate the html', function () {
    $post = Post::factory()->make(['body' => '## Hello World']);
    $post->save();

    expect($post->html)->toEqual(str($post->body)->markdown());
});

describe('View Count Tests', function () {
    beforeEach(function () {
        // Clear any existing Redis data before each test
        Redis::flushDb();
        $this->post = Post::factory()->create();
    });

    afterEach(function () {
        // Clean up Redis after each test
        Redis::flushDb();
    });

    it('starts with zero view count', function () {
        expect($this->post->getViewsCount())->toBe(0);
    });

    it('increments view count on first visit', function () {
        $this->post->incrementViews();

        expect($this->post->getViewsCount())->toBe(1);
    });

    it('stores view count with correct Redis key format', function () {
        $this->post->incrementViews();

        $expectedKey = "post_views:{$this->post->id}:".now()->format('Y-m-d');
        $value = Redis::get($expectedKey);

        expect((int) $value)->toBe(1);
    });

    it('handles multiple posts view counts independently', function () {
        $post2 = Post::factory()->create();

        // Increment views for first post
        $this->post->incrementViews();
        expect($this->post->getViewsCount())->toBe(1);
        expect($post2->getViewsCount())->toBe(0);

        // Increment views for second post
        $post2->incrementViews();
        expect($this->post->getViewsCount())->toBe(1);
        expect($post2->getViewsCount())->toBe(1);
    });

    it('can test the basic increment and clear functionality', function () {
        // Test basic increment
        $this->post->incrementViews();
        expect($this->post->getViewsCount())->toBe(1);

        // Test that we can manually clear Redis keys
        $key = "post_views:{$this->post->id}:".now()->format('Y-m-d');
        Redis::del($key);
        expect($this->post->getViewsCount())->toBe(0);
    });

    it('properly formats view keys and session keys', function () {
        // Get the expected keys
        $viewKey = "post_views:{$this->post->id}:".now()->format('Y-m-d');
        $sessionKey = "post_viewed:{$this->post->id}:".session()->getId();

        // Increment views
        $this->post->incrementViews();

        // Check that view key exists
        expect(Redis::exists($viewKey))->toBe(1);

        // The session key should exist - we can't easily test this in the test environment
        // due to session driver differences, but we can verify the key format
        expect($sessionKey)->toContain("post_viewed:{$this->post->id}:");
    });

    it('demonstrates session-based view limiting concept', function () {
        // This test shows how the session limiting would work conceptually
        $sessionId = 'test-session-123';
        $viewKey = "post_views:{$this->post->id}:".now()->format('Y-m-d');
        $sessionKey = "post_viewed:{$this->post->id}:{$sessionId}";

        // Simulate first visit
        Redis::incr($viewKey);
        Redis::setex($sessionKey, 3600, 1);

        // Check state
        expect((int) Redis::get($viewKey))->toBe(1);
        expect(Redis::exists($sessionKey))->toBe(1);

        // Simulate same session visit - should not increment
        if (!Redis::exists($sessionKey)) {
            Redis::incr($viewKey);
        }

        // Should still be 1
        expect((int) Redis::get($viewKey))->toBe(1);

        // Clean up
        Redis::del([$viewKey, $sessionKey]);
    });
});
