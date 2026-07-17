<?php
declare(strict_types=1);

namespace App\Api;

/**
 * @template TDto of object
 */
readonly class ResourceCollectionResponseDto
{
    public function __construct(
        public int $resultCount = 0,
        public int $totalResultCount = 0,
        public int $page = 0,
        public int $totalPageCount = 0,
        public array $result = [],
    ){}
}
