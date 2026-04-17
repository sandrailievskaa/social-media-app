<?php

namespace Database\Factories;

use App\Domain\Content\Enums\MediaType;
use App\Domain\Content\Models\Post;
use App\Domain\Content\Models\PostMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PostMedia>
 */
class PostMediaFactory extends Factory
{
    protected $model = PostMedia::class;

    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'file_path' => 'posts/'.fake()->uuid().'.jpg',
            'type' => fake()->randomElement([MediaType::Image, MediaType::Video]),
            'display_order' => 0,
            'alt_text' => fake()->boolean(25) ? fake()->sentence() : null,
        ];
    }
}
