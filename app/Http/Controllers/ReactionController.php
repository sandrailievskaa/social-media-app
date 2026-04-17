<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Not implemented yet.',
        ], 501);
    }
}
