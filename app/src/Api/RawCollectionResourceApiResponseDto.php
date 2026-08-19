<?php

declare(strict_types=1);

namespace App\Api;

readonly class RawCollectionResourceApiResponseDto
{
    /** @param RawSingleResourceApiResponseDto[] $results */
    public function __construct(
        public int $resultCount,
        public int $totalResultCount,
        public int $page,
        public int $totalPageCount,
        public array $results,
    ) {
    }
}
