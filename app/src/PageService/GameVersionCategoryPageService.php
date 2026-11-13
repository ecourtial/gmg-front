<?php

declare(strict_types=1);

namespace App\PageService;

use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\GameVersionCategoryAssociation;
use App\Entity\Dto\Specific\GameVersionCategoryPageDto;
use App\ResourceService\GameVersionCategoryAssociationService;
use App\ResourceService\GameVersionCategoryService;

readonly class GameVersionCategoryPageService
{
    public function __construct(
        private GameVersionCategoryService $gameVersionCategoryService,
        private GameVersionCategoryAssociationService $gameVersionCategoryAssociationService,
    ) {
    }

    public function getCategoryDetails(int $categoryId): GameVersionCategoryPageDto
    {
        $category = $this->gameVersionCategoryService->getById($categoryId);
        $associations = $this->gameVersionCategoryAssociationService->getListByCategoryId($categoryId);

        $associationsResults = $associations->result;

        usort($associationsResults, function (GameVersionCategoryAssociation $a, GameVersionCategoryAssociation $b) {
            return strcmp($a->gameTitle, $b->gameTitle);
        });

        return new GameVersionCategoryPageDto(
            $category,
            new ResourceCollectionResponseDto(
                $associations->resultCount,
                $associations->totalResultCount,
                $associations->page,
                $associations->totalPageCount,
                $associationsResults
            ),
        );
    }
}
