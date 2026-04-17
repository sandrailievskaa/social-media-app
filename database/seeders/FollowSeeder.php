<?php

namespace Database\Seeders;

use App\Domain\IdentityAndAccess\Models\Follow;
use App\Domain\IdentityAndAccess\Models\User;
use Illuminate\Database\Seeder;

class FollowSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->get();

        $users->each(function (User $user) use ($users): void {
            $targets = $users
                ->where('id', '!=', $user->getKey())
                ->random(fake()->numberBetween(4, 6));

            foreach ($targets as $target) {
                Follow::query()->firstOrCreate([
                    'follower_id' => $user->getKey(),
                    'following_id' => $target->getKey(),
                ]);
            }
        });

        $demo = User::query()->where('email', 'demo@example.com')->first();

        if ($demo instanceof User) {
            $targets = $users
                ->where('id', '!=', $demo->getKey())
                ->random(5);

            foreach ($targets as $target) {
                Follow::query()->firstOrCreate([
                    'follower_id' => $demo->getKey(),
                    'following_id' => $target->getKey(),
                ]);
            }
        }
    }
}
