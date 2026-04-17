<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user cannot follow themselves and gets 422', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->postJson(route('follows.store'), [
            'target_user_id' => $user->id,
        ])
        ->assertStatus(422);
});

test('user can follow and unfollow another user', function () {
    $me = User::factory()->create();
    $target = User::factory()->create();

    $this
        ->actingAs($me)
        ->postJson(route('follows.store'), [
            'target_user_id' => $target->id,
        ])
        ->assertOk();

    $this->assertDatabaseHas('follows', [
        'follower_id' => $me->id,
        'following_id' => $target->id,
    ]);

    $this
        ->actingAs($me)
        ->postJson(route('follows.store'), [
            'target_user_id' => $target->id,
        ])
        ->assertOk();

    $this->assertDatabaseMissing('follows', [
        'follower_id' => $me->id,
        'following_id' => $target->id,
    ]);
});
