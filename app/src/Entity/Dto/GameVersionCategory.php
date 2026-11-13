<?php

declare(strict_types=1);

namespace App\Entity\Dto;

readonly class GameVersionCategory
{
    public function __construct(
        public int $id,
        public string $name,
        public int $versionCount,
        public ?string $description = null,
    ) {
    }
}
