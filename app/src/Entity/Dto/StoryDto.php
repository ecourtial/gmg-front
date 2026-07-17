<?php
declare(strict_types=1);

namespace App\Entity\Dto;

readonly class StoryDto
{
    public function __construct(
        public int $id,
        public int $versionId,
        public int $year,
        public int $position,
        public bool $watched,
        public bool $played,
        public string $platformName,
        public string $gameTitle,
    ) {}
}
