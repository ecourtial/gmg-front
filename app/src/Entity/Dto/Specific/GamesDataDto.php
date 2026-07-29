<?php

declare(strict_types=1);

namespace App\Entity\Dto\Specific;

use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\GameDto;

readonly class GamesDataDto
{
    /** @param ResourceCollectionResponseDto<GameDto> $games */
    public function __construct(
        public ResourceCollectionResponseDto $games,
        public int $ownedCount,
    ) {
    }
}
