<?php

namespace App\Http\Controllers;

use App\Domain\Content\Models\Post;
use Illuminate\View\View;

class PostController extends Controller
{
    public function create(): View
    {
        return view('posts.create');
    }

    public function show(Post $post): View
    {
        return view('posts.show', [
            'post' => $post,
        ]);
    }
}
