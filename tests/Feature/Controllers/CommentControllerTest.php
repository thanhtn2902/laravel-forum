<?php
namespace Tests\Feature\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

it('required authentication to post a comment', function () {
    post(route('posts.comments.store', Post::factory()->create()))
        ->assertRedirect(route('login'));
});


it('can store a comment', function() {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    actingAs($user)->post(route('posts.comments.store', $post), [
        'body' => 'This is a comment',
    ]);

    $this->assertDatabaseHas(Comment::class, [
        'post_id' => $post->id,
        'user_id' => $user->id,
        'body'    => 'This is a comment'
    ]);

});

it('redirects to the post show page after create post', function() {
    $post = Post::factory()->create();

    actingAs(User::factory()->create())
        ->post(route('posts.comments.store', $post), [
            'body' => 'This is a comment',
        ])
        ->assertRedirect($post->showRoute());
});

it('required a valid body when create comment', function($value) {
    $post = Post::factory()->create();

    actingAs(User::factory()->create())
        ->post(route('posts.comments.store', $post), [
            'body' => $value,
        ])
        ->assertInvalid('body');
})->with([
    null,
    1,
    1.5,
    true,
    str_repeat('a', 2501)
]);

it('required authentication to delete comment', function () {
    delete(route('comments.destroy', Comment::factory()->create()))
        ->assertRedirect(route('login'));
});

it('can delete a comment', function() {
    $comment = Comment::factory()->create();

    actingAs($comment->user)->delete(route('comments.destroy', $comment));

    $this->assertModelMissing($comment);
});

it('redirect to show page when delete success', function() {
    $comment = Comment::factory()->create();

    actingAs($comment->user)
        ->delete(route('comments.destroy', $comment))
        ->assertRedirect($comment->post->showRoute());
});

it('prevent deleting a comment not belong to you', function() {
    $comment = Comment::factory()->create();

    actingAs(User::factory()->create())
        ->delete(route('comments.destroy', $comment->id))
        ->assertForbidden();
});

it('prevents deleting a comment posted over an hour ago', function() {
    $this->freezeTime();
    $comment = Comment::factory()->create();

    $this->travel(1)->hour();

    actingAs($comment->user)
        ->delete(route('comments.destroy', $comment->id))
        ->assertForbidden();
});

it('redirects to the post show page with query parameter', function() {
    $comment = Comment::factory()->create();

    actingAs($comment->user)
        ->delete(route('comments.destroy', ['comment' => $comment->id, 'page' => 2]))
        ->assertRedirect($comment->post->showRoute(['page' => 2]));
});

it('required authentication to update comment', function() {
    put(route('comments.update', Comment::factory()->create()))
        ->assertRedirect(route('login'));
});

it('can update a comment', function() {
    $comment = Comment::factory()->create(['body' => 'This is old post']);

    $newBody = 'This post has been update';

    actingAs($comment->user)
        ->put(route('comments.update', $comment), ['body' => $newBody]);

    $this->assertDatabaseHas(Comment::class, [
        'id' => $comment->id,
        'body' => $newBody
    ]);
});

it('redirect to the post show page after update post', function() {
    $comment = Comment::factory()->create();

    actingAs($comment->user)
        ->put(route('comments.update', $comment), ['body' => 'This comment has been update'])
        ->assertRedirect($comment->post->showRoute());
});

it('redirect to the correct page of comments', function() {
    $comment = Comment::factory()->create();

    actingAs($comment->user)
        ->put(route('comments.update', ['comment' => $comment, 'page' => 2]), ['body' => 'This post has been update'])
        ->assertRedirect($comment->post->showRoute(['page' => 2]));
});

it('cannot update comment from another user', function() {
    $comment = Comment::factory()->create();

    actingAs(User::factory()->create())
        ->put(route('comments.update', $comment), ['body' => 'This post has been update'])
        ->assertForbidden();
});

it('required a valid body when updating a comment', function($body) {
    $comment = Comment::factory()->create();

    actingAs($comment->user)
        ->put(route('comments.update', $comment), ['body' => $body])
        ->assertInvalid('body');
})
->with([
    null,
    1,
    1.5,
    true,
    str_repeat('a', 2501)
]);