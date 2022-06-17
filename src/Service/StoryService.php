<?php

declare(strict_types=1);

namespace App\Service;

class StoryService extends AbstractService
{
    public function getList(): array
    {
        $data = $this->clientFactory
            ->getAnonymousClient()
            ->get("stories?orderBy[]=year-asc&orderBy[]=position-asc&limit=" . self::MAX_RESULT_COUNT);

        $result = [
            'totalResultCount' => $data['totalResultCount'],
            'stories' => []
        ];

        foreach ($data['result'] as $entry) {
            if (false === \array_key_exists($entry['year'], $result['stories'])) {
                $result['stories'][$entry['year']] = [];
            }

            $result['stories'][$entry['year']][] = $entry;
        }

        return $result;
    }

    protected function getResourceType(): string
    {
        return 'story';
    }
}
