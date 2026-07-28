<?php
declare(strict_types=1);

namespace App\PageService;

use App\Entity\Dto\Specific\GameVersionMagazineMentionPageDto;
use App\ResourceService\GameMagazineMentionService;
use App\ResourceService\VersionService;

class MagazineIssuePageService
{
    public function __construct(
        private readonly GameMagazineMentionService $gameMagazineMentionService,
        private readonly VersionService $versionService,
    ){}

    public function getSortedMentions(int $issueId): array
    {
        $gameMentions = $this->gameMagazineMentionService->getByIssueId($issueId)->result;

        $gamesVersionsIds = [];
        foreach ($gameMentions as $gameMention) {
            $gamesVersionsIds[] = $gameMention->gameVersionId;
        }
        $gamesVersionsRaw = $this->versionService->getByIds($gamesVersionsIds)->result;

        $gamesVersions = [];
        foreach ($gamesVersionsRaw as $gameVersion) {
            $gamesVersions[$gameVersion->id] = $gameVersion;
        }

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

            $sortedMentions[$gameMentionType][$platformName][] = new GameVersionMagazineMentionPageDto(
                $gameMention->id,
                $gameMention->gameVersionId,
                $version->gameTitle,
                $gameMention->pageNumber,
                $gameMention->notes,
            );

            foreach ($sortedMentions as &$mentions) {
                foreach ($mentions as &$mentionByConsole) {
                    usort($mentionByConsole, function (GameVersionMagazineMentionPageDto $a, GameVersionMagazineMentionPageDto $b) { return strcmp($a->gameTitle, $b->gameTitle); });
                }
            }
            unset($mentions);
            unset($mentionByConsole);
        }

        return $sortedMentions;
    }
}
