<?php

namespace App\Http\Controllers;

use App\Domain\Content\Models\Post;
use App\Domain\Engagement\Actions\ToggleReactionAction;
use App\Domain\Engagement\Enums\ReactionType;
use App\Domain\Engagement\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'string', 'in:like,love,laugh,wow,sad,angry'],
            'post_id' => ['nullable', 'string', 'required_without:comment_id'],
            'comment_id' => ['nullable', 'string', 'required_without:post_id'],
        ]);

        $reactable = isset($data['post_id'])
            ? Post::query()->findOrFail($data['post_id'])
            : Comment::query()->findOrFail($data['comment_id']);

        $counts = app(ToggleReactionAction::class)->execute(
            $request->user(),
            $reactable,
            ReactionType::from($data['type']),
        );

        return response()->json([
            'counts' => $counts,
        ]);
    }
}
