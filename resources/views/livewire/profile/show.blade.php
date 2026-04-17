<?php

use App\Domain\IdentityAndAccess\Actions\ToggleFollowAction;
use App\Domain\IdentityAndAccess\Models\User;
use Livewire\Volt\Component;
use function Livewire\Volt\layout;

layout('layouts.app');

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

        $this->isFollowing = auth()->check()
            ? auth()->user()->isFollowing($this->profileUser)
            : false;
    }

    public function toggleFollow(): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login');

            return;
        }

        try {
            $result = app(ToggleFollowAction::class)->execute(auth()->user(), $this->profileUser);
            $this->isFollowing = $result;
        } catch (\InvalidArgumentException) {
            $this->dispatch('toast', message: 'You cannot follow yourself.');
        }

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

@php($title = $profileUser->name)
@php($avatarMeta = $profileUser->profile?->avatar_url ?: 'https://i.pravatar.cc/150?u='.urlencode($profileUser->email))
@push('head')
    <meta property="og:title" content="{{ $profileUser->name }}" />
    <meta property="og:image" content="{{ $avatarMeta }}" />
@endpush

<div class="pb-12">
    @php($cover = $profileUser->profile?->cover_url)
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm ring-1 ring-indigo-500/5">
            @if ($cover)
                <img src="{{ $cover }}" alt="" class="h-40 w-full object-cover" />
            @else
                <div class="h-40 w-full bg-gradient-to-r from-gray-100 to-gray-200"></div>
            @endif

            <div class="px-4 pb-6 sm:px-6">
                <div class="-mt-12 flex items-end justify-between gap-4">
                    <div class="flex items-end gap-4">
                        @php($avatar = $profileUser->profile?->avatar_url)
                        <img
                            src="{{ $avatar ?: 'https://i.pravatar.cc/150?u='.urlencode($profileUser->email) }}"
                            alt="{{ $profileUser->name }}"
                            class="h-24 w-24 rounded-full border-4 border-white bg-white object-cover shadow-sm"
                        />

                        <div class="pb-2">
                            <h1 class="text-2xl font-semibold text-gray-900">{{ $profileUser->name }}</h1>
                            @if (! blank($profileUser->profile?->bio))
                                <p class="mt-1 max-w-2xl text-sm leading-relaxed text-gray-700">{{ $profileUser->profile->bio }}</p>
                            @endif

                            <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-gray-600">
                                @if (! blank($profileUser->profile?->location))
                                    <span>{{ $profileUser->profile->location }}</span>
                                @endif
                                @if (! blank($profileUser->profile?->website))
                                    <a href="{{ $profileUser->profile->website }}" class="font-medium text-indigo-600 hover:text-indigo-700 hover:underline" target="_blank" rel="noreferrer">
                                        {{ $profileUser->profile->website }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="pb-2">
                        @auth
                            @if (auth()->id() === $profileUser->id)
                                <a href="{{ route('profile.edit') }}"
                                   class="inline-flex items-center rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-800 transition-all duration-200 hover:bg-gray-200 active:scale-95">
                                    Edit profile
                                </a>
                            @else
                                <button
                                    type="button"
                                    wire:click="toggleFollow"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm ring-1 ring-black/5 transition-all duration-200 disabled:opacity-50 active:scale-95
                                        {{ $isFollowing ? 'bg-emerald-500 hover:bg-emerald-600' : 'bg-indigo-500 hover:bg-indigo-600' }}"
                                >
                                    {{ $isFollowing ? 'Following' : 'Follow' }}
                                </button>
                            @endif
                        @else
                            <a href="{{ route('login') }}"
                               class="inline-flex items-center rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-800 transition-all duration-200 hover:bg-gray-200 active:scale-95">
                                Log in to follow
                            </a>
                        @endauth
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-6 text-sm">
                    <a href="{{ route('profile.followers', $profileUser) }}" class="group inline-flex items-baseline gap-2 font-medium text-gray-700 hover:text-gray-900">
                        <span class="text-lg font-semibold text-gray-900">{{ $followersCount }}</span>
                        <span class="text-sm text-gray-600 group-hover:text-indigo-700">Followers</span>
                    </a>
                    <a href="{{ route('profile.following', $profileUser) }}" class="group inline-flex items-baseline gap-2 font-medium text-gray-700 hover:text-gray-900">
                        <span class="text-lg font-semibold text-gray-900">{{ $followingCount }}</span>
                        <span class="text-sm text-gray-600 group-hover:text-indigo-700">Following</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="mx-auto mt-8 max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="space-y-6">
            @php($posts = $profileUser->posts()->latest()->with(['author.profile', 'media'])->get())
            @forelse ($posts as $post)
                <livewire:components.post-card :post="$post" :key="$post->id" />
            @empty
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-600 shadow-sm">
                    No posts yet.
                </div>
            @endforelse
        </div>
    </div>
</div>

