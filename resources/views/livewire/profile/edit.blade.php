<?php

use App\Domain\IdentityAndAccess\Actions\UpdateProfileAction;
use App\Domain\IdentityAndAccess\DTOs\UpdateProfileDTO;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use function Livewire\Volt\layout;

layout('layouts.app');

new class extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $bio = '';
    public string $location = '';
    public string $website = '';

    public $avatar = null;
    public $cover = null;

    public function mount(): void
    {
        $this->name = (string) auth()->user()->name;
        $profile = auth()->user()->profile;

        $this->bio = (string) ($profile?->bio ?? '');
        $this->location = (string) ($profile?->location ?? '');
        $this->website = (string) ($profile?->website ?? '');
    }

    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255', 'url'],
            'avatar' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,webp'],
            'cover' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,webp'],
        ]);

        $website = trim($this->website);
        if ($website !== '' && ! preg_match('/^https?:\\/\\//i', $website)) {
            $website = 'https://'.$website;
        }

        auth()->user()->forceFill(['name' => $this->name])->save();

        $dto = new UpdateProfileDTO(
            bio: $this->bio === '' ? null : $this->bio,
            location: $this->location === '' ? null : $this->location,
            website: $website === '' ? null : $website,
            avatar: $this->avatar,
            cover: $this->cover,
        );

        app(UpdateProfileAction::class)->execute(auth()->user(), $dto);

        session()->flash('toast', 'Profile updated.');

        $this->redirectRoute('profile.show', auth()->user());
    }
};

?>

@php($title = 'Edit profile')
<div class="py-8">
    <div class="mx-auto max-w-2xl space-y-6 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Edit profile</h1>
                <p class="mt-1 text-sm text-gray-600">Update your public info and images.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('profile.show', auth()->user()) }}"
                   class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-800 transition-all duration-200 hover:bg-gray-200 active:scale-95">
                    Back
                </a>
                <button type="submit" form="profile-edit-form" wire:loading.attr="disabled"
                        class="inline-flex items-center rounded-lg bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow-sm ring-1 ring-indigo-500/10 transition-all duration-200 hover:bg-indigo-600 hover:shadow-md disabled:opacity-50 active:scale-95">
                    Save
                </button>
            </div>
        </div>

        <form id="profile-edit-form" wire:submit.prevent="save" class="space-y-6 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
            <div class="grid gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input wire:model.defer="name" type="text"
                           class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm transition-all duration-200 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400" />
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Bio</label>
                    <textarea wire:model.defer="bio" rows="4"
                              class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm transition-all duration-200 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400"></textarea>
                    @error('bio') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Location</label>
                        <input wire:model.defer="location" type="text"
                               class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm transition-all duration-200 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400" />
                        @error('location') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Website</label>
                        <input wire:model.defer="website" type="text"
                               class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm transition-all duration-200 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400" />
                        @error('website') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Avatar</label>
                        <div class="mt-2 rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-4 transition-all duration-200 hover:border-indigo-300 hover:bg-indigo-50/40">
                            <div class="flex items-center gap-3">
                                @php($currentAvatar = auth()->user()->profile?->avatar_url)
                            @if ($avatar)
                                <img src="{{ $avatar->temporaryUrl() }}" class="h-12 w-12 rounded-full object-cover ring-2 ring-white" alt="" />
                            @else
                                <img src="{{ $currentAvatar ?: 'https://i.pravatar.cc/150?u='.urlencode(auth()->user()->email) }}"
                                     class="h-12 w-12 rounded-full object-cover ring-2 ring-white" alt="" />
                            @endif
                            <input wire:model="avatar" type="file"
                                   class="block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-500 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-indigo-600" />
                            </div>
                            <p class="mt-2 text-xs text-gray-500">JPG/PNG/WEBP up to 5MB.</p>
                        </div>
                        @error('avatar') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cover</label>
                        <div class="mt-2 rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-4 transition-all duration-200 hover:border-indigo-300 hover:bg-indigo-50/40">
                            @php($currentCover = auth()->user()->profile?->cover_url)
                            <div class="h-24 w-full overflow-hidden rounded-xl bg-gray-200">
                                @if ($cover)
                                    <img src="{{ $cover->temporaryUrl() }}" class="h-full w-full object-cover" alt="" />
                                @elseif ($currentCover)
                                    <img src="{{ $currentCover }}" class="h-full w-full object-cover" alt="" />
                                @endif
                            </div>
                            <input wire:model="cover" type="file"
                                   class="mt-2 block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-500 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-indigo-600" />
                            <p class="mt-2 text-xs text-gray-500">JPG/PNG/WEBP up to 5MB.</p>
                        </div>
                        @error('cover') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-4">
                <div wire:loading class="text-sm font-medium text-gray-600">Saving…</div>
                <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex items-center rounded-lg bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow-sm ring-1 ring-indigo-500/10 transition-all duration-200 hover:bg-indigo-600 hover:shadow-md disabled:opacity-50 active:scale-95">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

