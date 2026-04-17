<?php

use App\Domain\Content\Actions\CreatePostAction;
use App\Domain\Content\DTOs\CreatePostDTO;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use function Livewire\Volt\layout;

layout('layouts.app');

new class extends Component
{
    use WithFileUploads;

    public string $body = '';

    /**
     * @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile>
     */
    public array $mediaFiles = [];

    public function submit(): void
    {
        $this->validate([
            'body' => ['required', 'string', 'min:1'],
            'mediaFiles' => ['array'],
            'mediaFiles.*' => ['file', 'max:51200', 'mimes:jpg,jpeg,png,webp,mp4,webm'],
        ]);

        try {
            $dto = new CreatePostDTO(
                body: $this->body,
                mediaFiles: $this->mediaFiles,
            );

            app(CreatePostAction::class)->execute(auth()->user(), $dto);
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->errors());

            $this->redirect(url()->previous());

            return;
        }

        session()->flash('toast', 'Post created.');

        $this->redirectRoute('feed.index');
    }
};

?>

@php($title = 'Create post')
<div class="py-8">
    <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Create post</h1>
                <p class="mt-1 text-sm text-gray-600">Share something with your followers.</p>
            </div>
            <a href="{{ route('feed.index') }}" class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-800 transition-all duration-200 hover:bg-gray-200 active:scale-95">
                Back
            </a>
        </div>

        <form wire:submit.prevent="submit" class="mt-6 space-y-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
            <div>
                <label for="body" class="block text-sm font-medium text-gray-700">Body</label>
                <textarea
                    id="body"
                    wire:model.defer="body"
                    rows="4"
                    class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm transition-all duration-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400"
                    placeholder="What’s happening?"
                ></textarea>
                @error('body') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div
                x-data="{
                    previews: [],
                    setPreviews(files) {
                        this.previews = Array.from(files).map(f => ({
                            name: f.name,
                            type: f.type,
                            url: URL.createObjectURL(f),
                        }));
                    },
                }"
                x-on:livewire-upload-finish.window="$nextTick(() => setPreviews($refs.files.files))"
            >
                <label class="block text-sm font-medium text-gray-700">Media</label>
                <div class="mt-2 rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-4 transition-all duration-200 hover:border-indigo-300 hover:bg-indigo-50/40">
                    <input
                        x-ref="files"
                        type="file"
                        multiple
                        wire:model="mediaFiles"
                        class="block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-500 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-indigo-600"
                        x-on:change="setPreviews($event.target.files)"
                    />
                    <p class="mt-2 text-xs text-gray-500">JPG/PNG/WEBP up to 5MB each · MP4/WEBM up to 50MB.</p>
                </div>
                @error('mediaFiles.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

                <div class="mt-4 grid grid-cols-3 gap-3" x-show="previews.length" x-cloak>
                    <template x-for="p in previews" :key="p.url">
                        <div class="rounded-2xl border border-gray-200 bg-white p-2 shadow-sm">
                            <template x-if="p.type.startsWith('image/')">
                                <img :src="p.url" alt="" class="h-24 w-full rounded-xl object-cover" />
                            </template>
                            <template x-if="p.type.startsWith('video/')">
                                <div class="flex h-24 items-center justify-center rounded-xl bg-black text-xs font-semibold text-white">
                                    Video
                                </div>
                            </template>
                            <div class="mt-1 truncate text-xs text-gray-600" x-text="p.name"></div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center rounded-lg bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-indigo-600 disabled:opacity-50 active:scale-95"
                >
                    Publish
                </button>
                <div wire:loading class="text-sm text-gray-600">Uploading…</div>
            </div>
        </form>
    </div>
</div>

