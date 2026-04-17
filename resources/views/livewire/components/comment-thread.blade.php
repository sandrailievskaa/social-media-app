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
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm transition-all duration-200">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <img
                            src="{{ $avatar ?: 'https://i.pravatar.cc/150?u='.urlencode($comment->author->email) }}"
                            alt="{{ $comment->author->name }}"
                            class="h-8 w-8 rounded-full object-cover"
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
                                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm transition-all duration-200 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400"
                                    ></textarea>
                                    @error("editing.$comment->id") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

                                    <div class="mt-2 flex items-center gap-2">
                                        <button type="button" wire:click="saveEdit('{{ $comment->id }}')"
                                                class="rounded-lg bg-indigo-500 px-3 py-1.5 text-xs font-semibold text-white transition-all duration-200 hover:bg-indigo-600 active:scale-95">
                                            Save
                                        </button>
                                        <button type="button" wire:click="cancelEdit('{{ $comment->id }}')"
                                                class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-800 transition-all duration-200 hover:bg-gray-200 active:scale-95">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            @else
                                <p class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-gray-900">{{ $comment->body }}</p>
                            @endif

                            @php($counts = $reactionCountsFor($comment))
                            @php($active = $userReactionFor($comment))
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                @foreach ($reactionLabels as $type => $emoji)
                                    @php($count = (int) ($counts[$type] ?? 0))
                                    <button
                                        type="button"
                                        wire:click="toggleCommentReaction('{{ $comment->id }}', '{{ $type }}')"
                                        class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold transition-all duration-200 active:scale-95
                                            {{ $active === $type ? 'border-indigo-200 bg-indigo-50 text-indigo-700' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-100' }}"
                                    >
                                        <span aria-hidden="true">{{ $emoji }}</span>
                                        <span class="tabular-nums">{{ $count }}</span>
                                    </button>
                                @endforeach
                            </div>

                            <div class="mt-3 flex items-center gap-3 text-xs font-medium text-gray-600">
                                <button type="button" wire:click="startReply('{{ $comment->id }}')" class="rounded-md px-2 py-1 transition-all duration-200 hover:bg-gray-100 hover:text-gray-900 active:scale-95">
                                    Reply
                                </button>

                                @if ($comment->user_id === auth()->id())
                                    <button type="button" wire:click="startEdit('{{ $comment->id }}')" class="rounded-md px-2 py-1 transition-all duration-200 hover:bg-gray-100 hover:text-gray-900 active:scale-95">
                                        Edit
                                    </button>
                                    <button type="button"
                                            x-on:click="if (confirm('Delete this comment?')) { $wire.deleteComment('{{ $comment->id }}') }"
                                            class="rounded-md px-2 py-1 text-red-600 transition-all duration-200 hover:bg-red-50 hover:text-red-700 active:scale-95">
                                        Delete
                                    </button>
                                @endif
                            </div>

                            @php($replies = ($repliesByParent[$comment->id] ?? collect())->sortBy('created_at')->values())
                            @if ($replies->isNotEmpty())
                                <div class="mt-4 space-y-3 border-l-2 border-gray-200 pl-4">
                                    @foreach ($replies as $reply)
                                        @php($replyAvatar = $reply->author?->profile?->avatar_url)
                                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-3">
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
                                                                class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold transition-all duration-200 active:scale-95
                                                                    {{ $replyActive === $type ? 'border-indigo-200 bg-indigo-50 text-indigo-700' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-100' }}"
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
                                                                    class="rounded-md px-2 py-1 text-red-600 transition-all duration-200 hover:bg-red-50 hover:text-red-700 active:scale-95">
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
                                <div class="mt-4 rounded-2xl border border-gray-200 bg-white p-3 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-semibold text-gray-700">Reply</div>
                                        <button type="button" wire:click="cancelReply" class="text-xs text-gray-600 hover:text-gray-900">
                                            Cancel
                                        </button>
                                    </div>
                                    <textarea
                                        wire:model.defer="body"
                                        rows="3"
                                        x-on:keydown.enter="if (! $event.shiftKey) { $event.preventDefault(); $wire.addComment(); }"
                                        class="mt-2 w-full rounded-lg border-gray-300 text-sm shadow-sm transition-all duration-200 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400"
                                        placeholder="Write a reply…"
                                    ></textarea>
                                    @error('body') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    <div class="mt-2">
                                        <button type="button" wire:click="addComment"
                                                class="rounded-lg bg-indigo-500 px-3 py-1.5 text-xs font-semibold text-white transition-all duration-200 hover:bg-indigo-600 active:scale-95">
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
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-6 text-center text-sm text-gray-600 shadow-sm">
                Be the first to comment.
            </div>
        @endforelse
    </div>

    @if ($this->parentCommentId === null)
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <h3 class="text-sm font-semibold text-gray-900">Add a comment</h3>
                <div wire:loading class="text-xs font-medium text-gray-500">Posting…</div>
            </div>
            <textarea
                wire:model.defer="body"
                rows="4"
                x-on:keydown.enter="if (! $event.shiftKey) { $event.preventDefault(); $wire.addComment(); }"
                class="mt-2 w-full rounded-lg border-gray-300 text-sm shadow-sm transition-all duration-200 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400"
                placeholder="Write a comment…"
            ></textarea>
            @error('body') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <button type="button" wire:click="addComment"
                        wire:loading.attr="disabled"
                        class="rounded-lg bg-indigo-500 px-4 py-2 text-sm font-semibold text-white transition-all duration-200 hover:bg-indigo-600 disabled:opacity-50 active:scale-95">
                    Post comment
                </button>
            </div>
        </div>
    @endif
</div>

