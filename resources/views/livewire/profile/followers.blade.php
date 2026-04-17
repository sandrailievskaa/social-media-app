<?php

use App\Domain\IdentityAndAccess\Actions\ToggleFollowAction;
use App\Domain\IdentityAndAccess\Models\User;
use Livewire\Volt\Component;
use function Livewire\Volt\layout;

layout('layouts.app');

new class extends Component
{
    public User $profileUser;

    /**
     * @var \Illuminate\Support\Collection<int, User>
     */
    public $followers;

    /**
     * @var array<string, bool>
     */
    public array $followingMap = [];

    public function mount(User $user): void
    {
        $this->profileUser = $user;

        $this->followers = $user->followers()
            ->with('follower.profile')
            ->latest()
            ->get()
            ->map(fn ($follow) => $follow->follower);

        $this->hydrateFollowingMap();
    }

    private function hydrateFollowingMap(): void
    {
        if (! auth()->check()) {
            $this->followingMap = [];

            return;
        }

        $ids = $this->followers->pluck('id')->filter()->values()->all();

        if ($ids === []) {
            $this->followingMap = [];

            return;
        }

        $followingIds = auth()->user()->follows()
            ->whereIn('following_id', $ids)
            ->pluck('following_id')
            ->all();

        $map = [];
        foreach ($ids as $id) {
            $map[$id] = in_array($id, $followingIds, true);
        }

        $this->followingMap = $map;
    }

    public function toggleFollow(string $targetUserId): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login');

            return;
        }

        $target = User::query()->findOrFail($targetUserId);

        try {
            $followed = app(ToggleFollowAction::class)->execute(auth()->user(), $target);
        } catch (\InvalidArgumentException) {
            $this->dispatch('toast', message: 'You cannot follow yourself.');

            return;
        }

        $this->followingMap[$targetUserId] = $followed;
    }
};

?>

@php($title = $profileUser->name.' · Followers')
<div class="py-8">
    <div class="mx-auto max-w-2xl space-y-6 px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Followers</h1>
                <p class="mt-1 text-sm text-gray-600">{{ $profileUser->name }}</p>
            </div>
            <a href="{{ route('profile.show', $profileUser) }}" class="text-sm font-medium text-gray-700 hover:text-gray-900">
                Back
            </a>
        </div>

        <div class="space-y-3">
            @forelse ($followers as $u)
                <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-4">
                    <a href="{{ route('profile.show', $u) }}" class="flex min-w-0 items-center gap-3">
                        @php($avatar = $u->profile?->avatar_url)
                        <img
                            src="{{ $avatar ?: 'https://i.pravatar.cc/150?u='.urlencode($u->email) }}"
                            alt="{{ $u->name }}"
                            class="h-10 w-10 rounded-full object-cover"
                            loading="lazy"
                        />
                        <div class="min-w-0">
                            <div class="truncate text-sm font-semibold text-gray-900 hover:underline">{{ $u->name }}</div>
                            <div class="truncate text-xs text-gray-500">{{ $u->email }}</div>
                        </div>
                    </a>

                    @auth
                        @if (auth()->id() !== $u->id)
                            <button
                                type="button"
                                wire:click="toggleFollow('{{ $u->id }}')"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm disabled:opacity-50
                                    {{ ($followingMap[$u->id] ?? false) ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-900 hover:bg-gray-800' }}"
                            >
                                {{ ($followingMap[$u->id] ?? false) ? 'Following' : 'Follow' }}
                            </button>
                        @endif
                    @else
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Log in
                        </a>
                    @endauth
                </div>
            @empty
                <div class="rounded-lg border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-600">
                    No followers yet.
                </div>
            @endforelse
        </div>
    </div>
</div>
