<?php
declare(strict_types=1);

namespace App\Entity\Dto\Specific;

readonly class GameVersionMentionListDto
{
    /** @param array<string, non-empty-list<GameVersionMentionDetailsDto>> | array<string, non-empty-array<string, list<GameVersionMentionDetailsDto>>> $data */
    public function __construct(public array $data) {}
}
