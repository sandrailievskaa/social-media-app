<?php

use App\Domain\Content\Models\Post;
use App\Domain\Engagement\Actions\ToggleReactionAction;
use App\Domain\Engagement\Enums\ReactionType;
use Livewire\Volt\Component;
use function Livewire\Volt\layout;

layout('layouts.app');

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

        $reaction = $this->post->reactions()
            ->where('user_id', auth()->id())
            ->first();

        $this->userReaction = $reaction?->type?->value;
    }
};

?>

@php($title = 'Post')
<div class="py-8">
    <div class="mx-auto max-w-2xl space-y-6 px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            <a href="{{ route('feed.index') }}" class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-800 transition-all duration-200 hover:bg-gray-200 active:scale-95">
                Back
            </a>
        </div>

        <article class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
            <div class="flex items-start gap-3">
                <a href="{{ route('profile.show', $post->author) }}">
                    @php($avatar = $post->author?->profile?->avatar_url)
                    <img
                        src="{{ $avatar ?: 'https://i.pravatar.cc/150?u='.urlencode($post->author->email) }}"
                        alt="{{ $post->author->name }}"
                        class="h-10 w-10 rounded-full object-cover"
                        loading="lazy"
                    />
                </a>

                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <a href="{{ route('profile.show', $post->author) }}" class="text-sm font-semibold text-gray-900 hover:underline">
                                {{ $post->author->name }}
                            </a>
                            <div class="text-xs text-gray-500">{{ $post->created_at->diffForHumans() }}</div>
                        </div>
                    </div>

                    @if (! blank($post->body))
                        <div class="mt-4 whitespace-pre-wrap text-base leading-relaxed text-gray-900">{{ $post->body }}</div>
                    @endif

                    <div class="mt-4">
                        @include('livewire.components.media-gallery', ['media' => $post->media])
                    </div>

                    <div class="mt-4 border-t border-gray-100 pt-4">
                        @include('livewire.components.reaction-bar', [
                            'counts' => $reactionCounts,
                            'active' => $userReaction,
                        ])
                    </div>
                </div>
            </div>
        </article>

        <div>
            <h2 class="text-lg font-medium text-gray-900">Comments</h2>
            <div class="mt-3">
                <livewire:components.comment-thread :post="$post" />
            </div>
        </div>
    </div>
</div>

