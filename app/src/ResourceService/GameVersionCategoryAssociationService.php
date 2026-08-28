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
    public function getListByCategoryId(int $categoryId): ResourceCollectionResponseDto
    {
        return $this->getCollection('categoryId='.$categoryId.'&limit='.self::MAX_RESULT_COUNT);
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
}
