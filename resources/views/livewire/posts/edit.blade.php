<?php

use App\Domain\Content\Models\Post;
use Livewire\Volt\Component;
use function Livewire\Volt\layout;

layout('layouts.app');

new class extends Component
{
    public Post $post;
    public string $body = '';

    public function mount(Post $post): void
    {
        $this->post = $post;
        abort_unless(auth()->id() === $post->user_id, 403);

        $this->body = (string) $post->body;
    }

    public function save(): void
    {
        $this->validate([
            'body' => ['required', 'string', 'min:1'],
        ]);

        $this->post->forceFill([
            'body' => $this->body,
        ])->save();

        session()->flash('toast', 'Post updated.');

        $this->redirectRoute('posts.show', $this->post);
    }
};

?>

@php($title = 'Edit post')
<div class="py-8">
    <div class="mx-auto max-w-2xl space-y-6 px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-900">Edit post</h1>
            <a href="{{ route('posts.show', $post) }}" class="text-sm font-medium text-gray-700 hover:text-gray-900">
                Back
            </a>
        </div>

        <form wire:submit.prevent="save" class="space-y-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div>
                <label for="body" class="block text-sm font-medium text-gray-700">Body</label>
                <textarea
                    id="body"
                    wire:model.defer="body"
                    rows="5"
                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
                ></textarea>
                @error('body') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 disabled:opacity-50">
                    Save
                </button>
                <div wire:loading class="text-sm text-gray-600">Saving…</div>
            </div>
        </form>
    </div>
</div>

