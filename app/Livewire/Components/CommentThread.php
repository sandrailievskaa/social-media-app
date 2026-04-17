<?php

namespace App\Livewire\Components;

use App\Domain\Content\Models\Post;
use App\Domain\Engagement\Actions\CreateCommentAction;
use App\Domain\Engagement\Actions\ToggleReactionAction;
use App\Domain\Engagement\Enums\ReactionType;
use App\Domain\Engagement\Models\Comment;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class CommentThread extends Component
{
    public Post $post;

    public string $body = '';

    public ?string $parentCommentId = null;

    /**
     * @var array<string, string>
     */
    public array $editing = [];

    /**
     * @var array<string, string>
     */
    public array $replying = [];

    public function addComment(): void
    {
        $this->validate([
            'body' => ['required', 'string', 'min:1'],
        ]);

        app(CreateCommentAction::class)->execute(
            auth()->user(),
            $this->post,
            $this->body,
            $this->parentCommentId,
        );

        $this->reset(['body', 'parentCommentId']);
    }

    public function startReply(string $commentId): void
    {
        $this->parentCommentId = $commentId;
    }

    public function cancelReply(): void
    {
        $this->parentCommentId = null;
    }

    public function startEdit(string $commentId): void
    {
        $comment = Comment::query()->whereKey($commentId)->firstOrFail();

        if ($comment->user_id !== auth()->id()) {
            abort(403);
        }

        $this->editing[$commentId] = $comment->body;
    }

    public function cancelEdit(string $commentId): void
    {
        unset($this->editing[$commentId]);
    }

    public function saveEdit(string $commentId): void
    {
        $body = $this->editing[$commentId] ?? '';

        if (trim($body) === '') {
            throw ValidationException::withMessages([
                "editing.$commentId" => ['Body is required.'],
            ]);
        }

        $comment = Comment::query()->whereKey($commentId)->firstOrFail();

        if ($comment->user_id !== auth()->id()) {
            abort(403);
        }

        $comment->update([
            'body' => $body,
        ]);

        unset($this->editing[$commentId]);
    }

    public function deleteComment(string $commentId): void
    {
        $comment = Comment::query()->whereKey($commentId)->firstOrFail();

        if ($comment->user_id !== auth()->id()) {
            abort(403);
        }

        $comment->delete();
    }

    public function toggleCommentReaction(string $commentId, string $type): void
    {
        $comment = Comment::query()->whereKey($commentId)->firstOrFail();
        $reactionType = ReactionType::from($type);

        app(ToggleReactionAction::class)->execute(auth()->user(), $comment, $reactionType);
    }

    public function render(): View
    {
        $post = $this->post->loadMissing([
            'comments.author.profile',
            'comments.replies.author.profile',
            'comments.reactions',
            'comments.replies.reactions',
        ]);

        $topLevel = $post->comments
            ->whereNull('parent_comment_id')
            ->sortBy('created_at')
            ->values();

        $repliesByParent = $post->comments
            ->whereNotNull('parent_comment_id')
            ->groupBy('parent_comment_id');

        return view('livewire.components.comment-thread', [
            'topLevel' => $topLevel,
            'repliesByParent' => $repliesByParent,
        ]);
    }
}
