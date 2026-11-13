<?php

declare(strict_types=1);

namespace App\Entity\Dto\Specific;

use App\Entity\Dto\StoryDto;

readonly class StoriesOrderedByYearDto
{
    /** @param array<int, list<StoryDto>> $stories */
    public function __construct(
        public array $stories,
        public int $totalResultCount,
    ) {
    }
}
