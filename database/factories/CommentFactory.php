<?php

namespace Database\Factories;

use App\Domain\Content\Models\Post;
use App\Domain\Engagement\Models\Comment;
use App\Domain\IdentityAndAccess\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'user_id' => User::factory(),
            'parent_comment_id' => null,
            'body' => fake()->paragraph(),
        ];
    }
}
