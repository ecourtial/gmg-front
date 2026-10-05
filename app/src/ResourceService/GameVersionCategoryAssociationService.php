<?php

declare(strict_types=1);

namespace App\ResourceService;

use App\Api\RawSingleResourceApiResponseDto;
use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\GameVersionCategoryAssociation;

/**
 * @extends AbstractService<GameVersionCategoryAssociation>
 */
class GameVersionCategoryAssociationService extends AbstractService
{
    /**
     * @return ResourceCollectionResponseDto<GameVersionCategoryAssociation>
     */
    public function getListByCategoryId(int $categoryId): ResourceCollectionResponseDto
    {
        /** @TODO could we do this sorting on the API side? */
        $associations = $this->getCollection('categoryId[]='.$categoryId.'&limit='.self::MAX_RESULT_COUNT);
        $data = $associations->result;

        return $this->getSortedByName($associations, $data);
    }

    /**
     * @return ResourceCollectionResponseDto<GameVersionCategoryAssociation>
     */
    public function getListByVersionId(int $gameVersionId): ResourceCollectionResponseDto
    {
        /** @TODO could we do this sorting on the API side? */
        $associations = $this->getCollection('versionId[]='.$gameVersionId.'&limit='.self::MAX_RESULT_COUNT);
        $data = $associations->result;

        return $this->getSortedByName($associations, $data);
    }

    protected function getResourceNamePlural(): string
    {
        return 'game-version-category-associations';
    }

    protected function hydrateObject(RawSingleResourceApiResponseDto $dto): GameVersionCategoryAssociation
    {
        return new GameVersionCategoryAssociation(
            (int) $dto->data['id'],
            (int) $dto->data['categoryId'],
            (int) $dto->data['versionId'],
            (string) $dto->data['categoryName'],
            (string) $dto->data['versionPlatformName'],
            (string) $dto->data['gameTitle'],
            isset($dto->data['notes']) ? (string) $dto->data['notes'] : null,
        );
    }

    /**
     * @param ResourceCollectionResponseDto<GameVersionCategoryAssociation> $associations
     * @param GameVersionCategoryAssociation[]                              $data
     *
     * @return ResourceCollectionResponseDto<GameVersionCategoryAssociation>
     */
    protected function getSortedByName(ResourceCollectionResponseDto $associations, array $data): ResourceCollectionResponseDto
    {
        usort($data, function (GameVersionCategoryAssociation $a, GameVersionCategoryAssociation $b) {
            return strcmp($a->categoryName, $b->categoryName);
        });

        return new ResourceCollectionResponseDto(
            $associations->resultCount,
            $associations->totalResultCount,
            $associations->page,
            $associations->totalPageCount,
            $data
        );
    }
}
