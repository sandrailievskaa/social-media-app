<?php

use App\Domain\Content\Models\Post;
use App\Domain\Engagement\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can reply to a comment one level deep', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $parentComment = Comment::factory()->create([
        'post_id' => $post->id,
        'parent_comment_id' => null,
    ]);

    $response = $this
        ->actingAs($user)
        ->postJson(route('comments.store'), [
            'post_id' => $post->id,
            'body' => 'Reply body',
            'parent_comment_id' => $parentComment->id,
        ]);

    $response->assertCreated();

    $this->assertDatabaseHas('comments', [
        'post_id' => $post->id,
        'parent_comment_id' => $parentComment->id,
        'body' => 'Reply body',
    ]);

    $reply = Comment::query()->where('body', 'Reply body')->firstOrFail();

    expect($reply->parent_comment_id)->toBe($parentComment->id);
});
