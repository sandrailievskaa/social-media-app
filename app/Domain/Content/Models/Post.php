<?php

namespace App\Domain\Content\Models;

use App\Domain\Engagement\Models\Comment;
use App\Domain\Engagement\Models\Reaction;
use App\Domain\IdentityAndAccess\Models\User;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, HasUlids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'body',
    ];

    protected static function newFactory(): PostFactory
    {
        return PostFactory::new();
    }

    protected static function booted(): void
    {
        static::deleting(function (self $post): void {
            $post->comments()->get()->each->delete();
            $post->reactions()->delete();
        });
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(PostMedia::class)->orderBy('display_order');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }

    /**
     * @param  array<int, string>  $userIds
     */
    public function scopeForFeed(Builder $query, array $userIds): Builder
    {
        return $query
            ->whereIn('user_id', $userIds)
            ->latest();
    }

    public function scopePopular(Builder $query): Builder
    {
        return $query
            ->withCount('reactions')
            ->orderByDesc('reactions_count');
    }
}
