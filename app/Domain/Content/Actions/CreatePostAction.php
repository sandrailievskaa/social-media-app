<?php

namespace App\Domain\Content\Actions;

use App\Domain\Content\DTOs\CreatePostDTO;
use App\Domain\Content\Models\Post;
use App\Domain\Content\Services\MediaUploadService;
use App\Domain\IdentityAndAccess\Models\User;
use Illuminate\Support\Facades\DB;

class CreatePostAction
{
    public function __construct(
        private readonly MediaUploadService $mediaUploadService,
    ) {}

    public function execute(User $user, CreatePostDTO $dto): Post
    {
        return DB::transaction(function () use ($user, $dto): Post {
            $post = Post::query()->create([
                'user_id' => $user->getKey(),
                'body' => $dto->body,
            ]);

            if ($dto->mediaFiles !== []) {
                $this->mediaUploadService->storePostMedia($post, $dto->mediaFiles);
            }

            return $post;
        });
    }
}
