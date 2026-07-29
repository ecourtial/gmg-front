<?php

declare(strict_types=1);

namespace App\Entity\Dto;

readonly class GameVersionMentionDto
{
    public function __construct(
        public int $id,
        public int $magazineIssueId,
        public int $gameVersionId,
        public string $type,
        public int $pageNumber,
        public ?string $notes = null,
    ) {
    }
}
