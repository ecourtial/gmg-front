<?php
declare(strict_types=1);

namespace App\Entity\Dto\Specific;

readonly class GameVersionMentionDetailsDto
{
    public function __construct(
        public int $mentionId,
        public string $magazineTitle,
        public int $magazineIssueId,
        public int $magazineIssueYear,
        public int $magazineIssueMonth,
        public int $magazineIssueNumber,
        public int $pageNumber,
        public ?string $notes = null,
    ){}
}
