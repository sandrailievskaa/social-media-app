<?php

use App\Domain\Content\Services\FeedService;
use App\Domain\Engagement\Actions\ToggleReactionAction;
use App\Domain\Engagement\Enums\ReactionType;
use App\Domain\Content\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Volt\Component;
use function Livewire\Volt\layout;

layout('layouts.app');

new class extends Component
{
    public int $perPage = 15;

    public bool $isDiscover = false;

    public function loadMore(): void
    {
        $this->perPage += 15;
    }

    public function toggleReaction(string $postId, string $type): void
    {
        $post = Post::query()->findOrFail($postId);

        $reactionType = ReactionType::from($type);

        app(ToggleReactionAction::class)->execute(auth()->user(), $post, $reactionType);
    }

    public function getPostsProperty(): LengthAwarePaginator
    {
        $user = auth()->user();

        $feed = app(FeedService::class)->getFeed($user, $this->perPage);
        $this->isDiscover = $user->follows()->count() === 0;

        return $feed;
    }
};

?>

@php($title = 'Feed')
<div class="py-8">
    <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Feed</h1>
                @if ($isDiscover)
                    <p class="mt-1 text-sm text-gray-600">
                        <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">Discover</span>
                        <span class="ml-2">Popular posts while you follow people.</span>
                    </p>
                @endif
            </div>

            <a href="{{ route('posts.create') }}"
               class="inline-flex items-center rounded-lg bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-indigo-600 active:scale-95">
                Create post
            </a>
        </div>

        <div class="mt-6 space-y-6">
            <div wire:loading class="space-y-4">
                @foreach (range(1, 3) as $i)
                    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-gray-200"></div>
                            <div class="flex-1 space-y-2">
                                <div class="h-3 w-32 rounded bg-gray-200"></div>
                                <div class="h-2 w-20 rounded bg-gray-200"></div>
                            </div>
                        </div>
                        <div class="mt-4 space-y-2">
                            <div class="h-3 w-full rounded bg-gray-200"></div>
                            <div class="h-3 w-11/12 rounded bg-gray-200"></div>
                            <div class="h-3 w-9/12 rounded bg-gray-200"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            @forelse ($this->posts as $post)
                <livewire:components.post-card :post="$post" :key="$post->id" />
            @empty
                <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-sm">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-50">
                        <svg class="h-8 w-8 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 15a4 4 0 0 1-4 4H7l-4 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z" />
                        </svg>
                    </div>

                    <h2 class="mt-4 text-base font-semibold text-gray-900">Your feed is empty</h2>
                    <p class="mt-1 text-sm text-gray-600">
                        @if ($isDiscover)
                            There are no posts to discover yet. Be the first to post.
                        @else
                            Follow people to see their posts here.
                        @endif
                    </p>

                    <div class="mt-5 flex items-center justify-center gap-3">
                        <a href="{{ route('posts.create') }}"
                           class="inline-flex items-center rounded-lg bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-indigo-600 active:scale-95">
                            Create a post
                        </a>
                        <a href="{{ route('profile.edit') }}"
                           class="inline-flex items-center rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-800 transition-all duration-200 hover:bg-gray-200 active:scale-95">
                            Find people to follow
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        @if ($this->posts->total() > $this->posts->count())
            <div class="mt-8 flex justify-center">
                <button type="button"
                        wire:click="loadMore"
                        wire:loading.attr="disabled"
                        class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-800 transition-all duration-200 hover:bg-gray-200 disabled:opacity-50 active:scale-95">
                    Load more
                </button>
            </div>
        @endif
    </div>
</div>

