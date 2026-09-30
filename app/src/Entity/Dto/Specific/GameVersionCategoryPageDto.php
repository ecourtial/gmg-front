<?php

declare(strict_types=1);

namespace App\Entity\Dto\Specific;

use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\GameVersionCategory;
use App\Entity\Dto\GameVersionCategoryAssociation;

readonly class GameVersionCategoryPageDto
{
    /**
     * @param ResourceCollectionResponseDto<GameVersionCategoryAssociation> $associations
     */
    public function __construct(
        public GameVersionCategory $category,
        public ResourceCollectionResponseDto $associations,
    ) {
    }
}
