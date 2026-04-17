<?php

namespace App\Domain\IdentityAndAccess\Actions;

use App\Domain\IdentityAndAccess\Models\Follow;
use App\Domain\IdentityAndAccess\Models\User;
use InvalidArgumentException;

class ToggleFollowAction
{
    public function execute(User $follower, User $target): bool
    {
        if ($follower->getKey() === $target->getKey()) {
            throw new InvalidArgumentException('A user cannot follow themselves.');
        }

        $existing = Follow::query()
            ->where('follower_id', $follower->getKey())
            ->where('following_id', $target->getKey())
            ->first();

        if ($existing !== null) {
            $existing->delete();

            return false;
        }

        Follow::query()->create([
            'follower_id' => $follower->getKey(),
            'following_id' => $target->getKey(),
        ]);

        return true;
    }
}
