<?php

namespace App\Http\Controllers;

use App\Domain\Content\Actions\CreatePostAction;
use App\Domain\Content\DTOs\CreatePostDTO;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PostStoreController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'min:1'],
            'mediaFiles' => ['array'],
            'mediaFiles.*' => ['file', 'max:51200', 'mimes:jpg,jpeg,png,webp,mp4,webm'],
        ]);

        try {
            app(CreatePostAction::class)->execute(
                $request->user(),
                new CreatePostDTO(
                    body: $data['body'],
                    mediaFiles: $data['mediaFiles'] ?? [],
                ),
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('feed.index');
    }
}
