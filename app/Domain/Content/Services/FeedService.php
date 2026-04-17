<?php

namespace App\Domain\Content\Services;

use App\Domain\Content\Models\Post;
use App\Domain\IdentityAndAccess\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FeedService
{
    public function getFeed(User $user, int $perPage = 15): LengthAwarePaginator
    {
        $followedIds = $user->follows()
            ->pluck('following_id')
            ->all();

        if ($followedIds === []) {
            return $this->getDiscoverFeed($perPage);
        }

        return Post::query()
            ->forFeed($followedIds)
            ->with([
                'author.profile',
                'media',
                'reactions',
            ])
            ->withCount('comments')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getDiscoverFeed(int $perPage = 15): LengthAwarePaginator
    {
        return Post::query()
            ->with([
                'author.profile',
                'media',
                'reactions',
            ])
            ->withCount(['comments', 'reactions'])
            ->orderByDesc('reactions_count')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
}
