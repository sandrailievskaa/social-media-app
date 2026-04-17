<?php

namespace App\Domain\IdentityAndAccess\DTOs;

use Illuminate\Http\UploadedFile;

readonly class UpdateProfileDTO
{
    public function __construct(
        public ?string $bio = null,
        public ?string $location = null,
        public ?string $website = null,
        public ?UploadedFile $avatar = null,
        public ?UploadedFile $cover = null,
    ) {}
}
