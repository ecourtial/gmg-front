<?php

declare(strict_types=1);

namespace App\Service;

class StoryService extends AbstractService
{
    /** @return array<string, mixed> */
    public function getList(): array
    {
        /** @var array{result: list<array<string, scalar>>, totalResultCount: int} $data */
        $data = $this->clientFactory
            ->getAnonymousClient()
            ->get('stories?orderBy[]=year-asc&orderBy[]=position-asc&limit='.self::MAX_RESULT_COUNT);

        $result = [
            'totalResultCount' => $data['totalResultCount'],
            'stories' => [],
        ];

        foreach ($data['result'] as $entry) {
            $year = strval($entry['year']);
            if (false === \array_key_exists($year, $result['stories'])) {
                $result['stories'][$year] = [];
            }

            $result['stories'][$year][] = $entry;
        }

        return $result;
    }

    protected function getResourceType(): string
    {
        return 'story';
    }
}
