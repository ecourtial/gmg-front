<?php
declare(strict_types=1);

namespace App\Entity\Dto\Specific;

readonly class VersionsByPriorityDto
{
    public function __construct(
        public array $withPriority,
        public array $withoutPriority,
        public int $ownedCount,
        public int $totalResultCount,
    ) {}
}
