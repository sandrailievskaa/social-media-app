<?php

use App\Domain\Content\Models\Post;
use App\Domain\Engagement\Actions\ToggleReactionAction;
use App\Domain\Engagement\Enums\ReactionType;
use Livewire\Volt\Component;

new class extends Component
{
    public Post $post;

    /**
     * @var array<string, int>
     */
    public array $reactionCounts = [];

    public ?string $userReaction = null;

    public function mount(Post $post): void
    {
        $this->post = $post->loadMissing(['author.profile', 'media']);
        $this->hydrateReactionState();
    }

    public function toggleReaction(string $type): void
    {
        $reactionType = ReactionType::from($type);

        app(ToggleReactionAction::class)->execute(auth()->user(), $this->post, $reactionType);

        $this->hydrateReactionState();
    }

    private function hydrateReactionState(): void
    {
        $this->reactionCounts = $this->post->reactions()
            ->selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->map(fn ($count) => (int) $count)
            ->toArray();

        $this->userReaction = $this->post->reactions()
            ->where('user_id', auth()->id())
            ->value('type');
    }
};

?>

<x-app-layout>
    <div class="py-8">
        <div class="mx-auto max-w-2xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <a href="{{ route('feed.index') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900">
                    Back to feed
                </a>
            </div>

            <article class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex items-start gap-3">
                    @php($avatar = $post->author?->profile?->avatar_path)
                    <img
                        src="{{ $avatar ?: 'https://i.pravatar.cc/150?u='.urlencode($post->author->email) }}"
                        alt="{{ $post->author->name }}"
                        class="h-10 w-10 rounded-full object-cover"
                        loading="lazy"
                    />

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-gray-900">{{ $post->author->name }}</div>
                                <div class="text-xs text-gray-500">{{ $post->created_at->diffForHumans() }}</div>
                            </div>
                        </div>

                        @if (! blank($post->body))
                            <div class="mt-3 whitespace-pre-wrap text-sm leading-6 text-gray-900">{{ $post->body }}</div>
                        @endif

                        <div class="mt-4">
                            @include('livewire.components.media-gallery', ['media' => $post->media])
                        </div>

                        <div class="mt-4">
                            @include('livewire.components.reaction-bar', [
                                'counts' => $reactionCounts,
                                'active' => $userReaction,
                            ])
                        </div>
                    </div>
                </div>
            </article>

            <div>
                <h2 class="text-sm font-semibold text-gray-900">Comments</h2>
                <div class="mt-3">
                    <livewire:components.comment-thread :post="$post" />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

