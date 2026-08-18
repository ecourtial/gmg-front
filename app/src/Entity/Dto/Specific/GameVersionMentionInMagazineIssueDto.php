<?php
declare(strict_types=1);

namespace App\Entity\Dto\Specific;

readonly class GameVersionMentionInMagazineIssueDto
{

    /** @param array<string, array<string, list<GameVersionMagazineMentionPageDto>>> $data */
    public function __construct(public array $data) {}
}
