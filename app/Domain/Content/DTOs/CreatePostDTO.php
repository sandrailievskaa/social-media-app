<?php

namespace App\Domain\Content\DTOs;

readonly class CreatePostDTO
{
    /**
     * @param  array<int, mixed>  $mediaFiles
     */
    public function __construct(
        public string $body,
        public array $mediaFiles = [],
    ) {}
}
