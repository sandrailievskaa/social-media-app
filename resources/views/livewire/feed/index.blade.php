<?php

use App\Domain\Content\Services\FeedService;
use App\Domain\Engagement\Actions\ToggleReactionAction;
use App\Domain\Engagement\Enums\ReactionType;
use App\Domain\Content\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Volt\Component;

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

<x-app-layout>
    <div class="py-8">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900">Feed</h1>
                    @if ($isDiscover)
                        <p class="mt-1 text-sm text-gray-600">Discover popular posts while you follow people.</p>
                    @endif
                </div>

                <a href="{{ route('posts.create') }}"
                   class="inline-flex items-center rounded-md bg-gray-900 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800">
                    Create post
                </a>
            </div>

            <div class="mt-6 space-y-6">
                <div wire:loading class="rounded-lg border border-gray-200 bg-white p-4 text-sm text-gray-600">
                    Loading…
                </div>

                @forelse ($this->posts as $post)
                    <livewire:components.post-card :post="$post" :key="$post->id" />
                @empty
                    <div class="rounded-lg border border-gray-200 bg-white p-6 text-center text-sm text-gray-600">
                        No posts yet.
                    </div>
                @endforelse
            </div>

            @if ($this->posts->total() > $this->posts->count())
                <div class="mt-8 flex justify-center">
                    <button type="button"
                            wire:click="loadMore"
                            wire:loading.attr="disabled"
                            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50">
                        Load more
                    </button>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

