<?php

use App\Domain\Content\Actions\CreatePostAction;
use App\Domain\Content\DTOs\CreatePostDTO;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public string $body = '';

    /**
     * @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile>
     */
    public array $mediaFiles = [];

    public function submit(): RedirectResponse
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

            return redirect()->back();
        }

        return redirect()->route('feed.index');
    }
};

?>

<x-app-layout>
    <div class="py-8">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900">Create post</h1>
                <a href="{{ route('feed.index') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900">
                    Back to feed
                </a>
            </div>

            <form wire:submit.prevent="submit" class="mt-6 space-y-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div>
                    <label for="body" class="block text-sm font-medium text-gray-700">Body</label>
                    <textarea
                        id="body"
                        wire:model.defer="body"
                        rows="4"
                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
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
                    <input
                        x-ref="files"
                        type="file"
                        multiple
                        wire:model="mediaFiles"
                        class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-gray-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-gray-800"
                        x-on:change="setPreviews($event.target.files)"
                    />
                    @error('mediaFiles.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

                    <div class="mt-3 grid grid-cols-3 gap-2" x-show="previews.length" x-cloak>
                        <template x-for="p in previews" :key="p.url">
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-2">
                                <template x-if="p.type.startsWith('image/')">
                                    <img :src="p.url" alt="" class="h-24 w-full rounded object-cover" />
                                </template>
                                <template x-if="p.type.startsWith('video/')">
                                    <div class="flex h-24 items-center justify-center rounded bg-black text-xs font-medium text-white">
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
                        class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 disabled:opacity-50"
                    >
                        Publish
                    </button>
                    <div wire:loading class="text-sm text-gray-600">Uploading…</div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

