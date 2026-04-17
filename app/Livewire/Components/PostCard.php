<?php

namespace App\Livewire\Components;

use App\Domain\Content\Models\Post;
use App\Domain\Engagement\Actions\ToggleReactionAction;
use App\Domain\Engagement\Enums\ReactionType;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class PostCard extends Component
{
    public Post $post;

    /**
     * @var array<string, int>
     */
    public array $reactionCounts = [];

    public ?string $userReaction = null;

    public function mount(): void
    {
        $this->hydrateReactionState();
    }

    public function toggleReaction(string $type): void
    {
        $reactionType = ReactionType::from($type);

        app(ToggleReactionAction::class)->execute(auth()->user(), $this->post, $reactionType);

        $this->hydrateReactionState();
    }

    public function deletePost(): void
    {
        if (auth()->id() !== $this->post->user_id) {
            abort(403);
        }

        $this->post->delete();

        $this->dispatch('toast', message: 'Post deleted.');

        $this->redirectRoute('feed.index');
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

    public function render(): View
    {
        $post = $this->post->loadMissing(['author.profile', 'media']);

        return view('livewire.components.post-card', [
            'post' => $post,
        ]);
    }
}
