<?php

use App\Domain\Content\Models\Post;
use App\Domain\IdentityAndAccess\Models\Follow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('feed only shows posts from followed users', function () {
    $me = User::factory()->create();
    $followed = User::factory()->create();
    $stranger = User::factory()->create();

    Follow::query()->create([
        'follower_id' => $me->id,
        'following_id' => $followed->id,
    ]);

    $followedPost = Post::factory()->create([
        'user_id' => $followed->id,
        'body' => 'Followed post body',
    ]);

    $strangerPost = Post::factory()->create([
        'user_id' => $stranger->id,
        'body' => 'Stranger post body',
    ]);

    $this
        ->actingAs($me)
        ->get(route('feed.index'))
        ->assertOk()
        ->assertSee($followedPost->body)
        ->assertDontSee($strangerPost->body);
});

test('feed falls back to discover when user follows nobody', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();

    Post::factory()->count(3)->create([
        'user_id' => $other->id,
        'body' => 'Discover post body',
    ]);

    $this
        ->actingAs($me)
        ->get(route('feed.index'))
        ->assertOk()
        ->assertSee('Discover post body');
});
