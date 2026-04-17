<?php

namespace App\Http\Controllers;

use App\Domain\Content\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostDestroyController extends Controller
{
    public function __invoke(Request $request, Post $post): RedirectResponse
    {
        abort_unless($request->user()->getKey() === $post->user_id, 403);

        $post->delete();

        return redirect()->route('feed.index')->with('toast', 'Post deleted.');
    }
}
