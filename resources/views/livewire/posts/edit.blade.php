<?php

use App\Domain\Content\Models\Post;
use Livewire\Volt\Component;

new class extends Component
{
    public Post $post;

    public function mount(Post $post): void
    {
        $this->post = $post;
        abort_unless(auth()->id() === $post->user_id, 403);
    }
};

?>

<x-app-layout>
    @php($title = 'Edit post')
    <div class="py-8">
        <div class="mx-auto max-w-2xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h1 class="text-xl font-semibold text-gray-900">Edit post</h1>
                <p class="mt-2 text-sm text-gray-600">Not implemented yet.</p>
                <a href="{{ route('posts.show', $post) }}" class="mt-4 inline-flex text-sm font-medium text-gray-900 hover:underline">
                    Back to post
                </a>
            </div>
        </div>
    </div>
</x-app-layout>

