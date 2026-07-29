<?php

declare(strict_types=1);

namespace App\Entity\Dto;

readonly class GameDto
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $notes = null,
        public int $versionCount,
    ) {
    }
}
