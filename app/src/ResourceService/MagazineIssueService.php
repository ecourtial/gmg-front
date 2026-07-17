<?php
declare(strict_types=1);

namespace App\ResourceService;

use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\MagazineIssueDto;

/**
 * @extends AbstractService<MagazineIssueDto>
 */
class MagazineIssueService extends AbstractService
{
    public function getByMagazine(int $magazineId): ResourceCollectionResponseDto
    {
        return $this->hydrateResultCollection(
            $this->clientFactory
                ->getAnonymousClient()
                ->get($this->getResourceType()."s?magazineId[]={$magazineId}&orderBy[]=year-asc&orderBy[]=month-asc&limit=".self::MAX_RESULT_COUNT)
        );
    }

    public function formatMentions(array $gameMentions, array $gamesVersions): array
    {
        $sortedMentions = [];

        foreach ($gameMentions as $gameMention) {
            $gameMentionType = $gameMention->type;

            // First we order by mention type.
            if (false === array_key_exists($gameMentionType, $sortedMentions)) {
                $sortedMentions[$gameMentionType] = [];
            }

            $version = $gamesVersions[$gameMention->gameVersionId];

            // Then we order by platform.
            $platformName = $version->platformName;
            if (false === array_key_exists($platformName, $sortedMentions[$gameMentionType])) {
                $sortedMentions[$gameMentionType][$platformName] = [];
            }

            $sortedMentions[$gameMentionType][$platformName][] = [
                'id' => $gameMention->id,
                'gameVersionId' => $gameMention->gameVersionId,
                'gameTitle' => $version->gameTitle,
                'pageNumber' => $gameMention->pageNumber,
                'notes' => $gameMention->notes,
            ];

            foreach ($sortedMentions as &$mentions) {
                foreach ($mentions as &$mentionByConsole) {
                    usort($mentionByConsole, function (array $a, array $b) { return strcmp($a['gameTitle'], $b['gameTitle']); });
                }
            }
            unset($mentions);
            unset($mentionByConsole);
        }

        return $sortedMentions;
    }

    protected function getResourceType(): string
    {
        return 'magazine-issue';
    }

    protected function hydrateObject(array $data): MagazineIssueDto
    {
        return new MagazineIssueDto(
            $data['id'],
            $data['magazineId'],
            $data['issueNumber'],
            $data['year'],
            $data['month'],
            $data['notes'],
        );
    }
}
