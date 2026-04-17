<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class FeedController extends Controller
{
    public function index(): View
    {
        return view('feed.index');
    }
}
