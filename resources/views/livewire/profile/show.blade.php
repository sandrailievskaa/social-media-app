<?php

use App\Domain\IdentityAndAccess\Actions\ToggleFollowAction;
use App\Domain\IdentityAndAccess\Models\User;
use Livewire\Volt\Component;

new class extends Component
{
    public User $profileUser;

    public bool $isFollowing = false;

    public int $followersCount = 0;

    public int $followingCount = 0;

    public function mount(User $user): void
    {
        $this->profileUser = $user->loadMissing(['profile']);
        $this->refreshCounts();
        $this->isFollowing = auth()->user()->isFollowing($this->profileUser);
    }

    public function toggleFollow(): void
    {
        $result = app(ToggleFollowAction::class)->execute(auth()->user(), $this->profileUser);
        $this->isFollowing = $result;
        $this->refreshCounts();
    }

    private function refreshCounts(): void
    {
        $this->profileUser->loadCount(['followers', 'follows']);
        $this->followersCount = (int) $this->profileUser->followers_count;
        $this->followingCount = (int) $this->profileUser->following_count;
    }
};

?>

<x-app-layout>
    @php($title = $profileUser->name)
    @php($avatarMeta = $profileUser->profile?->avatar_path ?: 'https://i.pravatar.cc/150?u='.urlencode($profileUser->email))
    @push('head')
        <meta property="og:title" content="{{ $profileUser->name }}" />
        <meta property="og:image" content="{{ $avatarMeta }}" />
    @endpush

    <div class="pb-12">
        <div class="bg-gray-200">
            @php($cover = $profileUser->profile?->cover_path)
            @if ($cover)
                <div class="h-52 w-full bg-cover bg-center" style="background-image: url('{{ $cover }}');"></div>
            @else
                <div class="h-52 w-full bg-gradient-to-r from-gray-200 to-gray-300"></div>
            @endif
        </div>

        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="-mt-10 flex items-end justify-between gap-4">
                <div class="flex items-end gap-4">
                    @php($avatar = $profileUser->profile?->avatar_path)
                    <img
                        src="{{ $avatar ?: 'https://i.pravatar.cc/150?u='.urlencode($profileUser->email) }}"
                        alt="{{ $profileUser->name }}"
                        class="h-20 w-20 rounded-full border-4 border-white bg-white object-cover shadow"
                    />

                    <div class="pb-2">
                        <h1 class="text-2xl font-semibold text-gray-900">{{ $profileUser->name }}</h1>
                        @if (! blank($profileUser->profile?->bio))
                            <p class="mt-1 max-w-2xl text-sm text-gray-700">{{ $profileUser->profile->bio }}</p>
                        @endif

                        <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-gray-600">
                            @if (! blank($profileUser->profile?->location))
                                <span>{{ $profileUser->profile->location }}</span>
                            @endif
                            @if (! blank($profileUser->profile?->website))
                                <a href="{{ $profileUser->profile->website }}" class="font-medium text-gray-900 hover:underline" target="_blank" rel="noreferrer">
                                    {{ $profileUser->profile->website }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="pb-2">
                    @if (auth()->id() === $profileUser->id)
                        <a href="{{ route('profile.edit') }}"
                           class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Edit profile
                        </a>
                    @else
                        <button
                            type="button"
                            wire:click="toggleFollow"
                            class="inline-flex items-center rounded-md px-4 py-2 text-sm font-semibold text-white shadow-sm
                                {{ $isFollowing ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-900 hover:bg-gray-800' }}"
                        >
                            {{ $isFollowing ? 'Following' : 'Follow' }}
                        </button>
                    @endif
                </div>
            </div>

            <div class="mt-6 flex items-center gap-4 text-sm">
                <button type="button" class="font-semibold text-gray-900">
                    {{ $followersCount }} Followers
                </button>
                <button type="button" class="font-semibold text-gray-900">
                    {{ $followingCount }} Following
                </button>
            </div>

            <div class="mt-8 space-y-6">
                @php($posts = $profileUser->posts()->latest()->with(['author.profile', 'media'])->get())
                @forelse ($posts as $post)
                    <livewire:components.post-card :post="$post" :key="$post->id" />
                @empty
                    <div class="rounded-lg border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-600">
                        No posts yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>

