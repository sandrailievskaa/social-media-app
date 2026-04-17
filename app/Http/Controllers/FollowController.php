<?php

namespace App\Http\Controllers;

use App\Domain\IdentityAndAccess\Actions\ToggleFollowAction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class FollowController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'target_user_id' => ['required', 'string'],
        ]);

        $target = User::query()->findOrFail($data['target_user_id']);

        try {
            $followed = app(ToggleFollowAction::class)->execute($request->user(), $target);
        } catch (InvalidArgumentException) {
            return response()->json([
                'message' => 'Cannot follow yourself.',
            ], 422);
        }

        return response()->json([
            'followed' => $followed,
        ]);
    }
}
