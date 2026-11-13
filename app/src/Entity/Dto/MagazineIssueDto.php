<?php

declare(strict_types=1);

namespace App\Entity\Dto;

readonly class MagazineIssueDto
{
    public function __construct(
        public int $id,
        public int $magazineId,
        public int $issueNumber,
        public int $year,
        public int $month,
        public ?string $notes = null,
    ) {
    }
}
