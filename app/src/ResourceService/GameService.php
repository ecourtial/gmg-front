<?php

declare(strict_types=1);

namespace App\ResourceService;

use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\GameDto;
use App\Entity\Dto\Specific\GamesDataDto;
use App\Entity\Dto\Specific\VersionsDataDto;

/**
 * @extends AbstractService<GameDto>
 */
class GameService extends AbstractService
{
    public const string WITH_COMMENTS_FILTER = 'withComments';

    public const array FILTERS = [
        self::WITH_COMMENTS_FILTER => [],
    ];

    /**
     * @return ResourceCollectionResponseDto<GameDto>
     */
    public function getFirst(): ResourceCollectionResponseDto
    {
        return $this->getCollection('page=1&limit=1');
    }

    public function getList(): GamesDataDto
    {
        $data = $this->getCollection('orderBy[]=title-asc&limit='.self::MAX_RESULT_COUNT);

        $count = 0;
        foreach ($data->result as $game) {
            $count += intval(strval($game->versionCount));
        }

        return new GamesDataDto($data, $count);
    }

    public function search(string $keywords): VersionsDataDto
    {
        $data = $this->getCollection("title[]={$keywords}&orderBy[]=title-asc&page=1&limit=".self::MAX_RESULT_COUNT);

        $versionCount = 0;
        foreach ($data->result as $result) {
            $versionCount += $result->versionCount;
        }

        return new VersionsDataDto($data, $versionCount);
    }

    protected function getResourceNamePlural(): string
    {
        return 'games';
    }

    protected function hydrateObject(array $data): GameDto
    {
        return new GameDto(
            (int) $data['id'],
            (string) $data['title'],
            isset($data['notes']) ? (string) $data['notes'] : null,
            (int) $data['versionCount'],
        );
    }
}
