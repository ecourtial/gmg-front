<?php

declare(strict_types=1);

namespace App\ResourceService;

use App\Api\RawSingleResourceApiResponseDto;
use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\GameVersionCategory;

/**
 * @extends AbstractService<GameVersionCategory>
 */
class GameVersionCategoryService extends AbstractService
{
    public function getList(): ResourceCollectionResponseDto
    {
        return $this->getCollection('orderBy[]=title-asc&limit='.self::MAX_RESULT_COUNT);
    }

    protected function getResourceNamePlural(): string
    {
        return 'game-version-categories';
    }

    protected function hydrateObject(RawSingleResourceApiResponseDto $dto): GameVersionCategory
    {
        return new GameVersionCategory(
            (int) $dto->data['id'],
            (string) $dto->data['name'],
            (int) $dto->data['versionCount'],
            isset($dto->data['description']) ? (string) $dto->data['description'] : null,
        );
    }
}
