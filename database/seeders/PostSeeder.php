<?php

namespace Database\Seeders;

use App\Domain\Content\Enums\MediaType;
use App\Domain\Content\Models\Post;
use App\Domain\Content\Models\PostMedia;
use App\Domain\IdentityAndAccess\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $random = 1;

        User::query()->get()->each(function (User $user) use (&$random): void {
            $textOnly = Post::query()->create([
                'user_id' => $user->getKey(),
                'body' => fake()->sentence(),
            ]);

            $singleImage = Post::query()->create([
                'user_id' => $user->getKey(),
                'body' => fake()->sentence(),
            ]);

            PostMedia::query()->create([
                'post_id' => $singleImage->getKey(),
                'file_path' => "https://picsum.photos/800/600?random={$random}",
                'type' => MediaType::Image,
                'display_order' => 0,
                'alt_text' => fake()->sentence(),
            ]);
            $random++;

            $multiImage = Post::query()->create([
                'user_id' => $user->getKey(),
                'body' => fake()->sentence(),
            ]);

            foreach (range(0, fake()->numberBetween(1, 2)) as $i) {
                PostMedia::query()->create([
                    'post_id' => $multiImage->getKey(),
                    'file_path' => "https://picsum.photos/800/600?random={$random}",
                    'type' => MediaType::Image,
                    'display_order' => $i,
                    'alt_text' => fake()->sentence(),
                ]);
                $random++;
            }

            $video = Post::query()->create([
                'user_id' => $user->getKey(),
                'body' => fake()->sentence(),
            ]);

            PostMedia::query()->create([
                'post_id' => $video->getKey(),
                'file_path' => 'https://samplelib.com/lib/preview/mp4/sample-5s.mp4',
                'type' => MediaType::Video,
                'display_order' => 0,
                'alt_text' => null,
            ]);

            unset($textOnly);
        });
    }
}
