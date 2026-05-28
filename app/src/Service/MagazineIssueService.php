<?php
declare(strict_types=1);

namespace App\Service;

class MagazineIssueService extends AbstractService
{
    /** @return array<string, mixed> */
    public function getByMagazine(int $magazineId): array
    {
        /** @var array{result: list<array<string, scalar>>, totalResultCount: int} $data */
        $data = $this->clientFactory
            ->getAnonymousClient()
            ->get("magazine-issues?magazineId[]={$magazineId}&orderBy[]=year-asc&orderBy[]=month-asc&limit=".self::MAX_RESULT_COUNT);

        return $data;
    }

    public function getByIds(array $ids): array
    {
        // if (empty($versionsIds)) return ['result' => []];
        $issuesFilter = '';
        foreach ($ids as $id) {
            $issuesFilter .= "&id[]=".$id;
        }

        /** @var array{result: list<array<string, scalar>>, totalResultCount: int} $data */
        $data = $this->clientFactory
            ->getAnonymousClient()
            ->get("magazine-issues?orderBy[]=id-asc&limit=".self::MAX_RESULT_COUNT.$issuesFilter);

        return $data;
    }

    public function formatMentions(array $gameMentions, array $gamesVersions): array
    {
        $sortedMentions = [];

        foreach ($gameMentions as $gameMention) {
            $gameMentionType = $gameMention['type'];

            // First we order by mention type.
            if (false === array_key_exists($gameMentionType, $sortedMentions)) {
                $sortedMentions[$gameMentionType] = [];
            }

            $version = $gamesVersions[$gameMention['gameVersionId']];

            // Then we order by platform.
            $platformName = $version['platformName'];
            if (false === array_key_exists($platformName, $sortedMentions[$gameMentionType])) {
                $sortedMentions[$gameMentionType][$platformName] = [];
            }

            $sortedMentions[$gameMentionType][$platformName][] = [
                'id' => $gameMention['id'],
                'gameVersionId' => $gameMention['gameVersionId'],
                'gameTitle' => $version['gameTitle'],
                'notes' => $gameMention['notes'],
            ];
        }

        return $sortedMentions;
    }

    protected function getResourceType(): string
    {
        return 'magazine-issue';
    }
}
