<?php

namespace App\Domain\Engagement\Actions;

use App\Domain\Content\Models\Post;
use App\Domain\Engagement\Models\Comment;
use App\Domain\IdentityAndAccess\Models\User;
use Illuminate\Support\Facades\DB;

class CreateCommentAction
{
    public function execute(User $user, Post $post, string $body, ?string $parentCommentId = null): Comment
    {
        return DB::transaction(function () use ($user, $post, $body, $parentCommentId): Comment {
            $parentId = null;

            if ($parentCommentId !== null) {
                $parent = Comment::query()
                    ->whereKey($parentCommentId)
                    ->where('post_id', $post->getKey())
                    ->firstOrFail();

                $parentId = $parent->parent_comment_id ?? $parent->getKey();
            }

            return Comment::query()->create([
                'post_id' => $post->getKey(),
                'user_id' => $user->getKey(),
                'parent_comment_id' => $parentId,
                'body' => $body,
            ]);
        });
    }
}
