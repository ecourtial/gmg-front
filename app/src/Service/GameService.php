<?php

declare(strict_types=1);

namespace App\Service;

class GameService extends AbstractService
{
    /** @return array<string, mixed> */
    public function getList(): array
    {
        /** @var array{result: list<array<string, scalar>>, totalResultCount: int} $data */
        $data = $this->clientFactory
            ->getAnonymousClient()
            ->get('games?orderBy[]=title-asc&limit='.self::MAX_RESULT_COUNT);

        $count = 0;
        foreach ($data['result'] as $game) {
            $count += intval(strval($game['versionCount']));
        }

        $data['versionCount'] = $count;

        return $data;
    }

    /** @return array<string, mixed> */
    public function search(string $keywords): array
    {
        /** @var array{result: list<array<string, scalar>>, totalResultCount: int} $data */
        $data = $this->clientFactory
            ->getAnonymousClient()
            ->get("games?title[]={$keywords}&orderBy[]=title-asc&page=1&limit=".self::MAX_RESULT_COUNT);

        $versionCount = 0;
        foreach ($data['result'] as $result) {
            $versionCount += intval($result['versionCount']);
        }

        $data['versionCount'] = $versionCount;

        return $data;
    }

    public function formatMentions(array $magazines, array $versions, array $mentions, array $issues): array
    {
        $mentionsData = [];

        foreach ($mentions as $mention) {
            $mentionType = $mention['type'];

            if (false === array_key_exists($mentionType, $mentionsData)) {
                $mentionsData[$mentionType] = [];
            }

            $magazineIssueId = $mention['magazineIssueId'];
            $issue = $issues[$magazineIssueId];
            $platformName = $versions[$mention['gameVersionId']]['platformName'];

            if (false === array_key_exists($platformName, $mentionsData[$mentionType])) {
                $mentionsData[$mentionType][$platformName] = [];
            }

            $mentionsData[$mentionType][$platformName][] = [
                'mentionId' => $mention['id'],
                'magazineTitle' => $magazines[$issue['magazineId']]['title'],
                'magazineIssueId' => $magazineIssueId,
                'magazineIssueYear' => $issue['year'],
                'magazineIssueMonth' => $issue['month'],
                'magazineIssueNumber' => $issue['issueNumber'],
                'notes' => $mention['notes'],
            ];
        }

        return $mentionsData;
    }

    protected function getResourceType(): string
    {
        return 'game';
    }
}
