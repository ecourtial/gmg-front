<?php

declare(strict_types=1);

namespace App\Entity\Dto;

readonly class GameVersionCategoryAssociation
{
    public function __construct(
        public int $id,
        public int $categoryId,
        public int $versionId,
        public string $categoryName,
        public string $versionPlatformName,
        public string $gameTitle,
        public ?string $notes = null,
    ) {
    }
}
