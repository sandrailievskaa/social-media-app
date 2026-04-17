<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Not implemented yet.',
        ], 501);
    }
}
