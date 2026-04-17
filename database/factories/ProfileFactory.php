<?php

namespace Database\Factories;

use App\Domain\IdentityAndAccess\Models\Profile;
use App\Domain\IdentityAndAccess\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profile>
 */
class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'bio' => fake()->paragraph(),
            'avatar_path' => null,
            'cover_path' => null,
            'location' => fake()->city(),
            'website' => fake()->url(),
        ];
    }
}
