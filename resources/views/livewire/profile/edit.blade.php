<?php

use App\Domain\IdentityAndAccess\Actions\UpdateProfileAction;
use App\Domain\IdentityAndAccess\DTOs\UpdateProfileDTO;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public string $bio = '';
    public string $location = '';
    public string $website = '';

    public $avatar = null;
    public $cover = null;

    public function mount(): void
    {
        $profile = auth()->user()->profile;

        $this->bio = (string) ($profile?->bio ?? '');
        $this->location = (string) ($profile?->location ?? '');
        $this->website = (string) ($profile?->website ?? '');
    }

    public function save()
    {
        $this->validate([
            'bio' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,webp'],
            'cover' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,webp'],
        ]);

        $dto = new UpdateProfileDTO(
            bio: $this->bio === '' ? null : $this->bio,
            location: $this->location === '' ? null : $this->location,
            website: $this->website === '' ? null : $this->website,
            avatar: $this->avatar,
            cover: $this->cover,
        );

        app(UpdateProfileAction::class)->execute(auth()->user(), $dto);

        session()->flash('toast', 'Profile updated.');

        return redirect()->route('profile.show', auth()->user());
    }
};

?>

<x-app-layout>
    @php($title = 'Edit profile')
    <div class="py-8">
        <div class="mx-auto max-w-2xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900">Edit profile</h1>
                <a href="{{ route('profile.show', auth()->user()) }}" class="text-sm font-medium text-gray-700 hover:text-gray-900">
                    Back
                </a>
            </div>

            <form wire:submit.prevent="save" class="space-y-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="grid gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Bio</label>
                        <textarea wire:model.defer="bio" rows="4"
                                  class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900"></textarea>
                        @error('bio') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Location</label>
                            <input wire:model.defer="location" type="text"
                                   class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900" />
                            @error('location') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Website</label>
                            <input wire:model.defer="website" type="text"
                                   class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900" />
                            @error('website') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Avatar</label>
                            <div class="mt-2 flex items-center gap-3">
                                @php($currentAvatar = auth()->user()->profile?->avatar_path)
                                @if ($avatar)
                                    <img src="{{ $avatar->temporaryUrl() }}" class="h-12 w-12 rounded-full object-cover" alt="" />
                                @else
                                    <img src="{{ $currentAvatar ?: 'https://i.pravatar.cc/150?u='.urlencode(auth()->user()->email) }}"
                                         class="h-12 w-12 rounded-full object-cover" alt="" />
                                @endif
                                <input wire:model="avatar" type="file"
                                       class="block w-full text-sm text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-gray-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-gray-800" />
                            </div>
                            @error('avatar') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cover</label>
                            <div class="mt-2 space-y-2">
                                @php($currentCover = auth()->user()->profile?->cover_path)
                                <div class="h-24 w-full overflow-hidden rounded-lg bg-gray-200">
                                    @if ($cover)
                                        <img src="{{ $cover->temporaryUrl() }}" class="h-full w-full object-cover" alt="" />
                                    @elseif ($currentCover)
                                        <img src="{{ $currentCover }}" class="h-full w-full object-cover" alt="" />
                                    @endif
                                </div>
                                <input wire:model="cover" type="file"
                                       class="block w-full text-sm text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-gray-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-gray-800" />
                            </div>
                            @error('cover') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
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
</x-app-layout>

