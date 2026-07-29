<?php

declare(strict_types=1);

namespace App\Entity\Dto\Specific;

readonly class StoriesOrderedByYearDto
{
    public function __construct(
        public array $stories,
        public int $totalResultCount,
    ) {
    }
}
