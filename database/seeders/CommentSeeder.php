<?php

namespace Database\Seeders;

use App\Domain\Content\Models\Post;
use App\Domain\Engagement\Models\Comment;
use App\Domain\IdentityAndAccess\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->get();

        Post::query()->get()->each(function (Post $post) use ($users): void {
            $commentCount = fake()->numberBetween(2, 5);

            $comments = collect();

            for ($i = 0; $i < $commentCount; $i++) {
                $comments->push(Comment::query()->create([
                    'post_id' => $post->getKey(),
                    'user_id' => $users->random()->getKey(),
                    'parent_comment_id' => null,
                    'body' => fake()->paragraph(),
                ]));
            }

            $comments->each(function (Comment $comment) use ($post, $users): void {
                if (fake()->boolean(30)) {
                    $replies = fake()->numberBetween(1, 2);

                    for ($r = 0; $r < $replies; $r++) {
                        Comment::query()->create([
                            'post_id' => $post->getKey(),
                            'user_id' => $users->random()->getKey(),
                            'parent_comment_id' => $comment->getKey(),
                            'body' => fake()->paragraph(),
                        ]);
                    }
                }
            });
        });
    }
}
