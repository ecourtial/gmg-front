<?php

declare(strict_types=1);

namespace App\ResourceService;

use App\Entity\Dto\Specific\StoriesOrderedByYearDto;
use App\Entity\Dto\StoryDto;

/**
 * @extends AbstractService<StoryDto>
 */
class StoryService extends AbstractService
{
    public function getListOrderedByYear(int $versionId = 0): StoriesOrderedByYearDto
    {
        $versionFilter = '';

        if (0 !== $versionId) {
            $versionFilter = '&versionId[]='.$versionId;
        }

        $data = $this->getCollection('orderBy[]=year-asc&orderBy[]=position-asc'.$versionFilter.'&limit='.self::MAX_RESULT_COUNT);

        $totalResultCount = $data->totalResultCount;
        $stories = [];

        foreach ($data->result as $entry) {
            $year = strval($entry->year);
            if (false === \array_key_exists($year, $stories)) {
                $stories[$year] = [];
            }

            $stories[$year][] = $entry;
        }

        return new StoriesOrderedByYearDto($stories, $totalResultCount);
    }

    protected function getResourceNamePlural(): string
    {
        return 'stories';
    }

    protected function hydrateObject(array $data): StoryDto
    {
        return new StoryDto(
            (int) $data['id'],
            (int) $data['versionId'],
            (int) $data['year'],
            (int) $data['position'],
            (bool) $data['watched'],
            (bool) $data['played'],
            (string) $data['platformName'],
            (string) $data['gameTitle'],
        );
    }
}
