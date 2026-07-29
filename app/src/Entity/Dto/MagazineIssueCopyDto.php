<?php

declare(strict_types=1);

namespace App\Entity\Dto;

readonly class MagazineIssueCopyDto
{
    public function __construct(
        public int $id,
        public int $magazineIssueId,
        public string $type,
        public ?string $notes = null,
    ) {
    }
}
