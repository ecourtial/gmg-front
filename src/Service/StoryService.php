<?php

declare(strict_types=1);

namespace App\Service;

class StoryService extends AbstractService
{
    public function getById(int $storyId): array
    {
        return $this->clientFactory
            ->getReadOnlyClient()
            ->get("story/{$storyId}");
    }

    public function getList(): array
    {
        $data = $this->clientFactory
            ->getReadOnlyClient()
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


    public function add(array $data): array
    {
        return $this->clientFactory->getReadWriteClient()->post(
            'story',
            [],
            $data
        );
    }

    public function update(int $id, array $data): array
    {
        return $this->clientFactory->getReadWriteClient()->patch(
            'story/' . $id,
            [],
            $data
        );
    }

    public function delete(int $versionId): void
    {
        parent::removeEntry('story', $versionId);
    }
}
