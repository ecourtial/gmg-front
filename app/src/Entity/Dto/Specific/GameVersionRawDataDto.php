<?php

declare(strict_types=1);

namespace App\Entity\Dto\Specific;

use App\Entity\Dto\GameVersionMentionDto;
use App\Entity\Dto\MagazineDto;
use App\Entity\Dto\MagazineIssueDto;

readonly class GameVersionRawDataDto
{
    /**
     * @param MagazineDto[]           $magazines
     * @param MagazineIssueDto[]      $issues
     * @param GameVersionMentionDto[] $mentions
     */
    public function __construct(
        public array $magazines,
        public array $issues,
        public array $mentions,
    ) {
    }
}
