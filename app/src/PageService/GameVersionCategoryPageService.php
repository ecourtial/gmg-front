<?php
declare(strict_types=1);

namespace App\PageService;

use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\GameVersionCategoryAssociation;
use App\Entity\Dto\GameVersionDto;
use App\Entity\Dto\Specific\GameVersionCategoryPageDto;
use App\ResourceService\GameVersionCategoryAssociationService;
use App\ResourceService\GameVersionCategoryService;
use App\ResourceService\VersionService;

readonly class GameVersionCategoryPageService
{
    public function __construct(
        private GameVersionCategoryService $gameVersionCategoryService,
        private GameVersionCategoryAssociationService $gameVersionCategoryAssociationService,
        private VersionService $versionService,
    ) {}

    public function getCategoryDetails(int $categoryId): GameVersionCategoryPageDto
    {
        $category = $this->gameVersionCategoryService->getById($categoryId);
        $associations =  $this->gameVersionCategoryAssociationService->getListByCategoryId($categoryId);

        $versionsIds = [];
        foreach ($associations->result as $associationResult) {
            $versionsIds[] = $associationResult->versionId;
        }
        $versions = $this->versionService->getByIds($versionsIds);

        $versionsResults = $versions->result;

        usort($versionsResults, function (GameVersionDto $a, GameVersionDto $b) {
            return strcmp($a->gameTitle, $b->gameTitle);
        });

        return new GameVersionCategoryPageDto(
            $category,
            $associations,
            new ResourceCollectionResponseDto(
                $versions->resultCount,
                $versions->totalResultCount,
                $versions->page,
                $versions->totalPageCount,
                $versionsResults
            ),
        );
    }

}
