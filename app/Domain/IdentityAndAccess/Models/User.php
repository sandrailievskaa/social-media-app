<?php

namespace App\Domain\IdentityAndAccess\Models;

use App\Domain\Content\Models\Post;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUlids, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function follows(): HasMany
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }

    public function followers(): HasMany
    {
        return $this->hasMany(Follow::class, 'following_id');
    }

    public function isFollowing(User $user): bool
    {
        return $this->follows()
            ->where('following_id', $user->getKey())
            ->exists();
    }

    public function getFollowersCountAttribute(): int
    {
        return (int) ($this->attributes['followers_count'] ?? 0);
    }

    public function getFollowingCountAttribute(): int
    {
        return (int) ($this->attributes['follows_count'] ?? 0);
    }
}
