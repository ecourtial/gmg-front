<?php

declare(strict_types=1);

namespace App\Entity\Dto\Specific;

use App\Entity\Dto\GameVersionDto;

readonly class VersionsByPriorityDto
{
    /**
     * @param array<non-falsy-string, non-empty-list<GameVersionDto>> $withPriority
     * @param list<GameVersionDto> $withoutPriority
     */
    public function __construct(
        public array $withPriority,
        public array $withoutPriority,
        public int $ownedCount,
        public int $totalResultCount,
    ) {
    }
}
