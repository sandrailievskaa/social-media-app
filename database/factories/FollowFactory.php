<?php

namespace Database\Factories;

use App\Domain\IdentityAndAccess\Models\Follow;
use App\Domain\IdentityAndAccess\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Follow>
 */
class FollowFactory extends Factory
{
    protected $model = Follow::class;

    public function configure(): static
    {
        return $this->afterMaking(function (Follow $follow): void {
            if ($follow->follower_id === null || $follow->following_id === null) {
                return;
            }

            if ($follow->follower_id === $follow->following_id) {
                $follow->following_id = User::factory()->create()->getKey();
            }
        });
    }

    public function definition(): array
    {
        return [
            'follower_id' => User::factory(),
            'following_id' => User::factory(),
        ];
    }
}
