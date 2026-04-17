@props(['topLevel', 'repliesByParent'])

@php
    $reactionLabels = [
        'like' => '👍',
        'love' => '❤️',
        'laugh' => '😂',
        'wow' => '😮',
        'sad' => '😢',
        'angry' => '😡',
    ];

    $reactionCountsFor = function ($comment) {
        return $comment->reactions
            ->groupBy('type')
            ->map(fn ($group) => (int) $group->count())
            ->toArray();
    };

    $userReactionFor = function ($comment) {
        return $comment->reactions
            ->firstWhere('user_id', auth()->id())
            ?->type?->value ?? $comment->reactions->firstWhere('user_id', auth()->id())?->type;
    };
@endphp

<div class="space-y-6">
    <div class="space-y-4">
        @forelse ($topLevel as $comment)
            @php($avatar = $comment->author?->profile?->avatar_url)
            <div class="rounded-lg border border-gray-200 bg-white p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <img
                            src="{{ $avatar ?: 'https://i.pravatar.cc/150?u='.urlencode($comment->author->email) }}"
                            alt="{{ $comment->author->name }}"
                            class="h-9 w-9 rounded-full object-cover"
                            loading="lazy"
                        />

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <div class="truncate text-sm font-semibold text-gray-900">{{ $comment->author->name }}</div>
                                <div class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</div>
                            </div>

                            @if (array_key_exists($comment->id, $this->editing))
                                <div class="mt-2">
                                    <textarea
                                        wire:model.defer="editing.{{ $comment->id }}"
                                        rows="3"
                                        class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900"
                                    ></textarea>
                                    @error("editing.$comment->id") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

                                    <div class="mt-2 flex items-center gap-2">
                                        <button type="button" wire:click="saveEdit('{{ $comment->id }}')"
                                                class="rounded-md bg-gray-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-gray-800">
                                            Save
                                        </button>
                                        <button type="button" wire:click="cancelEdit('{{ $comment->id }}')"
                                                class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            @else
                                <p class="mt-2 whitespace-pre-wrap text-sm text-gray-900">{{ $comment->body }}</p>
                            @endif

                            @php($counts = $reactionCountsFor($comment))
                            @php($active = $userReactionFor($comment))
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                @foreach ($reactionLabels as $type => $emoji)
                                    @php($count = (int) ($counts[$type] ?? 0))
                                    <button
                                        type="button"
                                        wire:click="toggleCommentReaction('{{ $comment->id }}', '{{ $type }}')"
                                        class="inline-flex items-center gap-1 rounded-full border px-3 py-1 text-xs transition
                                            {{ $active === $type ? 'border-gray-900 bg-gray-900 text-white' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50' }}"
                                    >
                                        <span aria-hidden="true">{{ $emoji }}</span>
                                        <span class="tabular-nums">{{ $count }}</span>
                                    </button>
                                @endforeach
                            </div>

                            <div class="mt-3 flex items-center gap-3 text-xs font-medium text-gray-600">
                                <button type="button" wire:click="startReply('{{ $comment->id }}')" class="hover:text-gray-900">
                                    Reply
                                </button>

                                @if ($comment->user_id === auth()->id())
                                    <button type="button" wire:click="startEdit('{{ $comment->id }}')" class="hover:text-gray-900">
                                        Edit
                                    </button>
                                    <button type="button"
                                            x-on:click="if (confirm('Delete this comment?')) { $wire.deleteComment('{{ $comment->id }}') }"
                                            class="text-red-600 hover:text-red-700">
                                        Delete
                                    </button>
                                @endif
                            </div>

                            @php($replies = ($repliesByParent[$comment->id] ?? collect())->sortBy('created_at')->values())
                            @if ($replies->isNotEmpty())
                                <div class="mt-4 space-y-3 border-l border-gray-200 pl-4">
                                    @foreach ($replies as $reply)
                                        @php($replyAvatar = $reply->author?->profile?->avatar_url)
                                        <div class="rounded-lg border border-gray-100 bg-gray-50 p-3">
                                            <div class="flex items-start gap-3">
                                                <img
                                                    src="{{ $replyAvatar ?: 'https://i.pravatar.cc/150?u='.urlencode($reply->author->email) }}"
                                                    alt="{{ $reply->author->name }}"
                                                    class="h-8 w-8 rounded-full object-cover"
                                                    loading="lazy"
                                                />
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-2">
                                                        <div class="truncate text-sm font-semibold text-gray-900">{{ $reply->author->name }}</div>
                                                        <div class="text-xs text-gray-500">{{ $reply->created_at->diffForHumans() }}</div>
                                                    </div>
                                                    <p class="mt-1 whitespace-pre-wrap text-sm text-gray-900">{{ $reply->body }}</p>

                                                    @php($replyCounts = $reactionCountsFor($reply))
                                                    @php($replyActive = $userReactionFor($reply))
                                                    <div class="mt-2 flex flex-wrap items-center gap-2">
                                                        @foreach ($reactionLabels as $type => $emoji)
                                                            @php($count = (int) ($replyCounts[$type] ?? 0))
                                                            <button
                                                                type="button"
                                                                wire:click="toggleCommentReaction('{{ $reply->id }}', '{{ $type }}')"
                                                                class="inline-flex items-center gap-1 rounded-full border px-3 py-1 text-xs transition
                                                                    {{ $replyActive === $type ? 'border-gray-900 bg-gray-900 text-white' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50' }}"
                                                            >
                                                                <span aria-hidden="true">{{ $emoji }}</span>
                                                                <span class="tabular-nums">{{ $count }}</span>
                                                            </button>
                                                        @endforeach
                                                    </div>

                                                    @if ($reply->user_id === auth()->id())
                                                        <div class="mt-2 flex items-center gap-3 text-xs font-medium text-gray-600">
                                                            <button type="button"
                                                                    x-on:click="if (confirm('Delete this comment?')) { $wire.deleteComment('{{ $reply->id }}') }"
                                                                    class="text-red-600 hover:text-red-700">
                                                                Delete
                                                            </button>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if ($this->parentCommentId === $comment->id)
                                <div class="mt-4 rounded-lg border border-gray-200 bg-white p-3">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-semibold text-gray-700">Reply</div>
                                        <button type="button" wire:click="cancelReply" class="text-xs text-gray-600 hover:text-gray-900">
                                            Cancel
                                        </button>
                                    </div>
                                    <textarea
                                        wire:model.defer="body"
                                        rows="3"
                                        class="mt-2 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900"
                                        placeholder="Write a reply…"
                                    ></textarea>
                                    @error('body') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    <div class="mt-2">
                                        <button type="button" wire:click="addComment"
                                                class="rounded-md bg-gray-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-gray-800">
                                            Reply
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-dashed border-gray-300 bg-white p-6 text-center text-sm text-gray-600">
                Be the first to comment.
            </div>
        @endforelse
    </div>

    @if ($this->parentCommentId === null)
        <div class="rounded-lg border border-gray-200 bg-white p-4">
            <h3 class="text-sm font-semibold text-gray-900">Add a comment</h3>
            <textarea
                wire:model.defer="body"
                rows="4"
                class="mt-2 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900"
                placeholder="Write a comment…"
            ></textarea>
            @error('body') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            <div class="mt-3 flex items-center gap-3">
                <button type="button" wire:click="addComment"
                        class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                    Post comment
                </button>
            </div>
        </div>
    @endif
</div>

