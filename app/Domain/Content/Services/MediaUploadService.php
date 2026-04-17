<?php

namespace App\Domain\Content\Services;

use App\Domain\Content\Enums\MediaType;
use App\Domain\Content\Models\Post;
use App\Domain\Content\Models\PostMedia;
use App\Domain\IdentityAndAccess\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MediaUploadService
{
    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function storePostMedia(Post $post, array $files): void
    {
        $this->validatePostMediaFiles($files);

        foreach (array_values($files) as $index => $file) {
            $type = $this->inferMediaType($file);
            $path = $this->storeOnPublicDisk("posts/{$post->getKey()}", $file);

            PostMedia::query()->create([
                'post_id' => $post->getKey(),
                'file_path' => $path,
                'type' => $type,
                'display_order' => $index,
                'alt_text' => null,
            ]);
        }
    }

    public function storeAvatar(UploadedFile $file, User $user): string
    {
        $this->validateAvatar($file);

        return $this->storeOnPublicDisk("avatars/{$user->getKey()}", $file);
    }

    public function storeCover(UploadedFile $file, User $user): string
    {
        $this->validateCover($file);

        return $this->storeOnPublicDisk("covers/{$user->getKey()}", $file);
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    private function validatePostMediaFiles(array $files): void
    {
        $errors = [];

        foreach ($files as $idx => $file) {
            if (! $file instanceof UploadedFile) {
                $errors["mediaFiles.$idx"][] = 'Invalid upload.';

                continue;
            }

            $type = $this->inferMediaType($file);

            if ($type === MediaType::Image) {
                if (! $this->hasAllowedExtension($file, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $errors["mediaFiles.$idx"][] = 'Image must be a jpg, png, or webp.';
                }

                if (! $this->isMaxKilobytes($file, 5120)) {
                    $errors["mediaFiles.$idx"][] = 'Image must be 5120 KB or smaller.';
                }
            }

            if ($type === MediaType::Video) {
                if (! $this->hasAllowedExtension($file, ['mp4', 'webm'])) {
                    $errors["mediaFiles.$idx"][] = 'Video must be an mp4 or webm.';
                }

                if (! $this->isMaxKilobytes($file, 51200)) {
                    $errors["mediaFiles.$idx"][] = 'Video must be 51200 KB or smaller.';
                }
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function validateAvatar(UploadedFile $file): void
    {
        $this->validateSingleImage($file, 'avatar', 5120);
    }

    private function validateCover(UploadedFile $file): void
    {
        $this->validateSingleImage($file, 'cover', 5120);
    }

    private function validateSingleImage(UploadedFile $file, string $field, int $maxKilobytes): void
    {
        $errors = [];

        if (! $this->hasAllowedExtension($file, ['jpg', 'jpeg', 'png', 'webp'])) {
            $errors[$field][] = 'Image must be a jpg, png, or webp.';
        }

        if (! $this->isMaxKilobytes($file, $maxKilobytes)) {
            $errors[$field][] = "Image must be {$maxKilobytes} KB or smaller.";
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function inferMediaType(UploadedFile $file): MediaType
    {
        $ext = strtolower((string) $file->getClientOriginalExtension());

        return match (true) {
            in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true) => MediaType::Image,
            in_array($ext, ['mp4', 'webm'], true) => MediaType::Video,
            default => throw ValidationException::withMessages([
                'mediaFiles' => ['Unsupported file type.'],
            ]),
        };
    }

    /**
     * @param  array<int, string>  $extensions
     */
    private function hasAllowedExtension(UploadedFile $file, array $extensions): bool
    {
        $ext = strtolower((string) $file->getClientOriginalExtension());

        return in_array($ext, $extensions, true);
    }

    private function isMaxKilobytes(UploadedFile $file, int $maxKilobytes): bool
    {
        $sizeBytes = $file->getSize() ?? 0;
        $sizeKb = (int) ceil($sizeBytes / 1024);

        return $sizeKb <= $maxKilobytes;
    }

    private function storeOnPublicDisk(string $directory, UploadedFile $file): string
    {
        $filename = $file->hashName();
        $path = trim($directory, '/').'/'.$filename;

        Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));

        return $path;
    }
}
