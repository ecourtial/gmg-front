<?php

declare(strict_types=1);

namespace App\PageService;

use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\GameVersionMentionDto;
use App\Entity\Dto\Specific\GameVersionMentionDetailsDto;
use App\Entity\Dto\Specific\GameVersionMentionListDto;
use App\Entity\Dto\Specific\GameVersionRawDataDto;
use App\Entity\Dto\Specific\VersionsDataDto;
use App\ResourceService\GameMagazineMentionService;
use App\ResourceService\MagazineIssueService;
use App\ResourceService\MagazineService;

class GameVersionPageService
{
    public function __construct(
        private readonly GameMagazineMentionService $gameMagazineMentionService,
        private readonly MagazineService $magazineService,
        private readonly MagazineIssueService $magazineIssueService,
    ) {
    }

    public function getVersionsWithComments(VersionsDataDto $data): VersionsDataDto
    {
        /**
         * Ugly! @TODO implement that on API side please.
         * On top of that we could have used a simple getList() from the service.
         * But at least it reminds us that we need to improve filters on the API side.
         */
        $results = $data->versions->result;
        $ownedCount = $data->ownedCount;

        foreach ($data->versions->result as $key => $item) {
            if (null === $item->comments
                || '' === trim($item->comments)) {
                if (0 < $item->copyCount) {
                    --$ownedCount;
                }
                unset($results[$key]);
            }
        }

        return new VersionsDataDto(
            new ResourceCollectionResponseDto(
                $data->versions->resultCount,
                $data->versions->totalResultCount,
                $data->versions->page,
                $data->versions->totalPageCount,
                $results
            ),
            $ownedCount
        );
    }

    public function getMentionsByType(int $versionId): GameVersionMentionListDto
    {
        $mentions = $this->gameMagazineMentionService->getByVersionId($versionId)->result;
        $rawMentions = $this->prepareMentions($mentions);

        $mentionsData = [];

        foreach ($rawMentions->mentions as $mention) {
            $mentionType = $mention->type;

            if (false === array_key_exists($mentionType, $mentionsData)) {
                $mentionsData[$mentionType] = [];
            }

            $magazineIssueId = $mention->magazineIssueId;
            $issue = $rawMentions->issues[$magazineIssueId];

            $mentionsData[$mentionType][] = new GameVersionMentionDetailsDto(
                $mention->id,
                $rawMentions->magazines[$issue->magazineId]->title,
                $magazineIssueId,
                $issue->year,
                $issue->month,
                $issue->issueNumber,
                $mention->pageNumber,
                $mention->notes,
            );
        }

        return new GameVersionMentionListDto($mentionsData);
    }

    /**
     * @param GameVersionMentionDto[] $mentions
     */
    public function prepareMentions(array $mentions): GameVersionRawDataDto
    {
        $issues = [];
        $magazines = [];

        $magazinesIds = [];
        $magazinesIssuesIds = [];

        foreach ($mentions as $mention) {
            $magazinesIssuesIds[] = $mention->magazineIssueId;
        }

        $issuesResult = $this->magazineIssueService->getByIds($magazinesIssuesIds)->result;

        foreach ($issuesResult as $magazineIssue) {
            $issues[$magazineIssue->id] = $magazineIssue;
            $magazinesIds[] = $magazineIssue->magazineId;
        }

        $magazinesResult = $this->magazineService->getByIds($magazinesIds)->result;

        foreach ($magazinesResult as $magazine) {
            $magazines[$magazine->id] = $magazine;
        }

        return new GameVersionRawDataDto($magazines, $issues, $mentions);
    }
}
