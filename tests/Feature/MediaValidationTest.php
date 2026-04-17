<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

test('image upload over 5MB is rejected', function () {
    $user = User::factory()->create();

    $file = UploadedFile::fake()->create('photo.jpg', 6000, 'image/jpeg');

    $this
        ->actingAs($user)
        ->from(route('posts.create'))
        ->post(route('posts.store'), [
            'body' => 'Hello',
            'mediaFiles' => [$file],
        ])
        ->assertSessionHasErrors('mediaFiles.0');
});

test('valid image under 5MB is accepted', function () {
    $user = User::factory()->create();

    $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

    $this
        ->actingAs($user)
        ->post(route('posts.store'), [
            'body' => 'Hello',
            'mediaFiles' => [$file],
        ])
        ->assertSessionHasNoErrors();
});
