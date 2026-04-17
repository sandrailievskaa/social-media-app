<?php

namespace App\Domain\Content\Models;

use App\Domain\Content\Enums\MediaType;
use Database\Factories\PostMediaFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
