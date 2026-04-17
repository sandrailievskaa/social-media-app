@props(['post'])

<article class="rounded-2xl border border-gray-200 bg-white shadow-sm transition-all duration-200 hover:shadow-md">
    <div class="p-4 sm:p-6">
        <div class="flex items-start justify-between gap-4">
            <a href="{{ route('profile.show', $post->author) }}" class="flex items-center gap-3">
                @php($avatar = $post->author?->profile?->avatar_url)
                <img
                    src="{{ $avatar ?: 'https://i.pravatar.cc/150?u='.urlencode($post->author->email) }}"
                    alt="{{ $post->author->name }}"
                    class="h-10 w-10 rounded-full object-cover"
                    loading="lazy"
                />

                <div>
                    <div class="text-sm font-semibold text-gray-900 hover:underline">
                        {{ $post->author->name }}
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ $post->created_at->diffForHumans() }}
                    </div>
                </div>
            </a>

            @if (auth()->id() === $post->user_id)
                <button type="button"
                        x-on:click="if (confirm('Delete this post?')) { $wire.deletePost() }"
                        class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-red-600 transition-all duration-200 hover:bg-gray-200 active:scale-95">
                    Delete
                </button>
            @endif
        </div>

        @if (! blank($post->body))
            <div class="mt-4 whitespace-pre-wrap text-base leading-relaxed text-gray-900">
                {{ $post->body }}
            </div>
        @endif

        <div class="mt-4">
            @include('livewire.components.media-gallery', ['media' => $post->media])
        </div>

        <div class="mt-4 border-t border-gray-100 pt-4">
            @include('livewire.components.reaction-bar', [
                'counts' => $this->reactionCounts,
                'active' => $this->userReaction,
            ])
        </div>

        <div class="mt-4 flex items-center justify-between text-sm text-gray-600">
            <a href="{{ route('posts.show', $post) }}" class="font-medium hover:text-gray-900">
                {{ $post->comments()->count() }} comments
            </a>

            <a href="{{ route('posts.show', $post) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                View
            </a>
        </div>
    </div>
</article>

