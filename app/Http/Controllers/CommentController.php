<?php

namespace App\Http\Controllers;

use App\Domain\Content\Models\Post;
use App\Domain\Engagement\Actions\CreateCommentAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'post_id' => ['required', 'string'],
            'body' => ['required', 'string', 'min:1'],
            'parent_comment_id' => ['nullable', 'string'],
        ]);

        $post = Post::query()->findOrFail($data['post_id']);

        $comment = app(CreateCommentAction::class)->execute(
            $request->user(),
            $post,
            $data['body'],
            $data['parent_comment_id'] ?? null,
        );

        return response()->json([
            'comment' => $comment->toArray(),
        ], 201);
    }
}
