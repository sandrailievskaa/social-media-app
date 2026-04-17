<?php

namespace Database\Seeders;

use App\Domain\IdentityAndAccess\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $demo = User::query()->create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => Hash::make('password'),
        ]);

        $demo->profile()->update([
            'bio' => fake()->paragraph(),
            'location' => fake()->city(),
            'website' => fake()->url(),
            'avatar_path' => "https://i.pravatar.cc/150?u={$demo->email}",
        ]);

        User::factory()
            ->count(11)
            ->create()
            ->each(function (User $user): void {
                $user->profile()->update([
                    'bio' => fake()->paragraph(),
                    'location' => fake()->city(),
                    'website' => fake()->url(),
                    'avatar_path' => "https://i.pravatar.cc/150?u={$user->email}",
                ]);
            });
    }
}
