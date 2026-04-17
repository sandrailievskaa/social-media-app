@props(['post'])

<article class="rounded-lg border border-gray-200 bg-white shadow-sm">
    <div class="p-4">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                @php($avatar = $post->author?->profile?->avatar_path)
                <img
                    src="{{ $avatar ?: 'https://i.pravatar.cc/150?u='.urlencode($post->author->email) }}"
                    alt="{{ $post->author->name }}"
                    class="h-10 w-10 rounded-full object-cover"
                    loading="lazy"
                />

                <div>
                    <div class="text-sm font-semibold text-gray-900">
                        {{ $post->author->name }}
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ $post->created_at->diffForHumans() }}
                    </div>
                </div>
            </div>

            @if (auth()->id() === $post->user_id)
                <button type="button" class="text-xs font-medium text-red-600 hover:text-red-700">
                    Delete
                </button>
            @endif
        </div>

        @if (! blank($post->body))
            <div class="mt-3 whitespace-pre-wrap text-sm leading-6 text-gray-900">
                {{ $post->body }}
            </div>
        @endif

        <div class="mt-4">
            @include('livewire.components.media-gallery', ['media' => $post->media])
        </div>

        <div class="mt-4">
            @include('livewire.components.reaction-bar', [
                'counts' => $this->reactionCounts,
                'active' => $this->userReaction,
            ])
        </div>

        <div class="mt-3 flex items-center justify-between text-sm text-gray-600">
            <a href="{{ route('posts.show', $post) }}" class="hover:text-gray-900">
                {{ $post->comments()->count() }} comments
            </a>
        </div>
    </div>
</article>

