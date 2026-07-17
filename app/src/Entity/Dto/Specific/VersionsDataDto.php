<?php
declare(strict_types=1);

namespace App\Entity\Dto\Specific;
use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\GameVersionDto;

readonly class VersionsDataDto
{
    /** @param ResourceCollectionResponseDto<GameVersionDto> $versions */
    public function __construct(
      public ResourceCollectionResponseDto $versions,
      public int $ownedCount,
    ){}
}
