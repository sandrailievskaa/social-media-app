<?php

namespace Database\Factories;

use App\Domain\Content\Models\Post;
use App\Domain\Engagement\Enums\ReactionType;
use App\Domain\Engagement\Models\Comment;
use App\Domain\Engagement\Models\Reaction;
use App\Domain\IdentityAndAccess\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reaction>
 */
class ReactionFactory extends Factory
{
    protected $model = Reaction::class;

    public function configure(): static
    {
        return $this->afterCreating(function (Reaction $reaction): void {
            if (! empty($reaction->reactable_id) && ! empty($reaction->reactable_type)) {
                return;
            }

            $post = Post::factory()->create();

            $reaction->forceFill([
                'reactable_type' => $post->getMorphClass(),
                'reactable_id' => $post->getKey(),
            ])->save();
        });
    }

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'reactable_type' => null,
            'reactable_id' => null,
            'type' => fake()->randomElement(ReactionType::cases()),
        ];
    }

    public function forPost(Post $post): static
    {
        return $this->state(fn () => [
            'reactable_type' => $post->getMorphClass(),
            'reactable_id' => $post->getKey(),
        ]);
    }

    public function forComment(Comment $comment): static
    {
        return $this->state(fn () => [
            'reactable_type' => $comment->getMorphClass(),
            'reactable_id' => $comment->getKey(),
        ]);
    }
}
