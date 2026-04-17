<?php

namespace App\Domain\IdentityAndAccess\Actions;

use App\Domain\Content\Services\MediaUploadService;
use App\Domain\IdentityAndAccess\DTOs\UpdateProfileDTO;
use App\Domain\IdentityAndAccess\Models\Profile;
use App\Domain\IdentityAndAccess\Models\User;

class UpdateProfileAction
{
    public function __construct(
        private readonly MediaUploadService $mediaUploadService,
    ) {}

    public function execute(User $user, UpdateProfileDTO $dto): Profile
    {
        $attributes = [
            'bio' => $dto->bio,
            'location' => $dto->location,
            'website' => $dto->website,
        ];

        if ($dto->avatar !== null) {
            $attributes['avatar_path'] = $this->mediaUploadService->storeAvatar($dto->avatar, $user);
        }

        if ($dto->cover !== null) {
            $attributes['cover_path'] = $this->mediaUploadService->storeCover($dto->cover, $user);
        }

        return Profile::query()->updateOrCreate(
            ['user_id' => $user->getKey()],
            $attributes,
        );
    }
}
