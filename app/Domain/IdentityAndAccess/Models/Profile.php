<?php

namespace App\Domain\IdentityAndAccess\Models;

use Database\Factories\ProfileFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    /** @use HasFactory<ProfileFactory> */
    use HasFactory, HasUlids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'bio',
        'avatar_path',
        'cover_path',
        'location',
        'website',
    ];

    protected static function newFactory(): ProfileFactory
    {
        return ProfileFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bio' => 'string',
            'avatar_path' => 'string',
            'cover_path' => 'string',
            'location' => 'string',
            'website' => 'string',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
