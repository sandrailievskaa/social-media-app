<?php

namespace App\Domain\Content\Services;

use App\Domain\Content\Models\Post;
use App\Domain\IdentityAndAccess\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FeedService
{
    public function getFeed(User $user, int $perPage = 15): LengthAwarePaginator
    {
        $followedIds = $user->follows()->pluck('following_id')->all();

        $base = Post::query()
            ->with([
                'author.profile',
                'media',
                'reactions',
            ])
            ->withCount('comments');

        // Always include your own posts in the feed.
        if ($followedIds !== []) {
            $ids = array_values(array_unique(array_merge($followedIds, [$user->getKey()])));

            return $base
                ->forFeed($ids)
                ->orderByDesc('created_at')
                ->paginate($perPage);
        }

        // If you follow nobody, fall back to "discover" while still showing your newest posts.
        return $base
            ->withCount('reactions')
            ->orderByDesc('created_at')
            ->orderByDesc('reactions_count')
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
            ->orderByDesc('created_at')
            ->orderByDesc('reactions_count')
            ->paginate($perPage);
    }
}
