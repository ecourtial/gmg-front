<?php
declare(strict_types=1);

namespace App\Entity\Dto\Specific;

use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\GameVersionCategory;
use App\Entity\Dto\GameVersionCategoryAssociation;
use App\Entity\Dto\GameVersionDto;

readonly class GameVersionCategoryPageDto
{
    /**
     * @param ResourceCollectionResponseDto<GameVersionCategoryAssociation> $associations
     * @param  ResourceCollectionResponseDto<GameVersionDto> $versions
     */
    public function __construct(
        public GameVersionCategory $category,
        public ResourceCollectionResponseDto $associations,
        public ResourceCollectionResponseDto $versions,
    ) {}
}
