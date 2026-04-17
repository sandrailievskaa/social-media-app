<?php

namespace App\Domain\Content\Models;

use App\Domain\Content\Enums\MediaType;
use Database\Factories\PostMediaFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PostMedia extends Model
{
    /** @use HasFactory<PostMediaFactory> */
    use HasFactory, HasUlids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'post_id',
        'file_path',
        'type',
        'display_order',
        'alt_text',
    ];

    protected static function newFactory(): PostMediaFactory
    {
        return PostMediaFactory::new();
    }

    protected static function booted(): void
    {
        static::deleting(function (self $media): void {
            if (str_starts_with($media->file_path, 'http://') || str_starts_with($media->file_path, 'https://')) {
                return;
            }

            Storage::disk('public')->delete($media->file_path);
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => MediaType::class,
            'display_order' => 'integer',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
