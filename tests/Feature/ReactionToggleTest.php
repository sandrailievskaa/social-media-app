<?php

use App\Domain\Content\Models\Post;
use App\Domain\Engagement\Models\Comment;
use App\Domain\Engagement\Models\Reaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can react to a post and reaction is created', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $response = $this
        ->actingAs($user)
        ->postJson(route('reactions.store'), [
            'post_id' => $post->id,
            'type' => 'like',
        ]);

    $response->assertOk();
    $response->assertJsonStructure(['counts']);

    $this->assertDatabaseHas('reactions', [
        'user_id' => $user->id,
        'reactable_type' => $post->getMorphClass(),
        'reactable_id' => $post->id,
        'type' => 'like',
    ]);
});

test('reacting with same type removes the reaction (toggle off)', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    Reaction::query()->create([
        'user_id' => $user->id,
        'reactable_type' => $post->getMorphClass(),
        'reactable_id' => $post->id,
        'type' => 'like',
    ]);

    $this
        ->actingAs($user)
        ->postJson(route('reactions.store'), [
            'post_id' => $post->id,
            'type' => 'like',
        ])
        ->assertOk();

    $this->assertDatabaseMissing('reactions', [
        'user_id' => $user->id,
        'reactable_type' => $post->getMorphClass(),
        'reactable_id' => $post->id,
        'type' => 'like',
    ]);
});

test('reacting with different type updates existing reaction', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    Reaction::query()->create([
        'user_id' => $user->id,
        'reactable_type' => $post->getMorphClass(),
        'reactable_id' => $post->id,
        'type' => 'like',
    ]);

    $this
        ->actingAs($user)
        ->postJson(route('reactions.store'), [
            'post_id' => $post->id,
            'type' => 'love',
        ])
        ->assertOk();

    $this->assertDatabaseHas('reactions', [
        'user_id' => $user->id,
        'reactable_type' => $post->getMorphClass(),
        'reactable_id' => $post->id,
        'type' => 'love',
    ]);

    expect(Reaction::query()->count())->toBe(1);
});

test('user can react to a comment (polymorphic)', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    $comment = Comment::factory()->create([
        'post_id' => $post->id,
    ]);

    $this
        ->actingAs($user)
        ->postJson(route('reactions.store'), [
            'comment_id' => $comment->id,
            'type' => 'like',
        ])
        ->assertOk();

    $this->assertDatabaseHas('reactions', [
        'user_id' => $user->id,
        'reactable_type' => $comment->getMorphClass(),
        'reactable_id' => $comment->id,
        'type' => 'like',
    ]);
});
