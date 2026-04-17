<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReactionController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return redirect()->route('feed.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Volt::route('/feed', 'feed.index')->name('feed.index');

    Volt::route('/posts/create', 'posts.create')->name('posts.create');
    Volt::route('/posts/{post}', 'posts.show')->name('posts.show');

    Volt::route('/profile/{user}', 'profile.show')->name('profile.show');

    Route::get('/profile', [ProfileController::class, 'edit']);
    Volt::route('/profile/edit', 'profile.edit')->name('profile.edit');

    Route::post('/reactions', ReactionController::class)->name('reactions.store');
    Route::post('/follows', FollowController::class)->name('follows.store');
    Route::post('/comments', CommentController::class)->name('comments.store');

    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
