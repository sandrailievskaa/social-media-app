<?php

namespace Database\Seeders;

use App\Domain\Content\Models\Post;
use App\Domain\Engagement\Enums\ReactionType;
use App\Domain\Engagement\Models\Comment;
use App\Domain\Engagement\Models\Reaction;
use App\Domain\IdentityAndAccess\Models\User;
use Illuminate\Database\Seeder;

class ReactionSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->get();

        Post::query()->get()->each(function (Post $post) use ($users): void {
            if (! fake()->boolean(80)) {
                return;
            }

            $reactors = $users->random(fake()->numberBetween(1, min(6, $users->count())));

            foreach ($reactors as $user) {
                Reaction::query()->updateOrCreate(
                    [
                        'user_id' => $user->getKey(),
                        'reactable_type' => $post->getMorphClass(),
                        'reactable_id' => $post->getKey(),
                    ],
                    [
                        'type' => fake()->randomElement(ReactionType::cases()),
                    ],
                );
            }
        });

        Comment::query()->get()->each(function (Comment $comment) use ($users): void {
            if (! fake()->boolean(40)) {
                return;
            }

            $reactors = $users->random(fake()->numberBetween(1, min(4, $users->count())));

            foreach ($reactors as $user) {
                Reaction::query()->updateOrCreate(
                    [
                        'user_id' => $user->getKey(),
                        'reactable_type' => $comment->getMorphClass(),
                        'reactable_id' => $comment->getKey(),
                    ],
                    [
                        'type' => fake()->randomElement(ReactionType::cases()),
                    ],
                );
            }
        });
    }
}
