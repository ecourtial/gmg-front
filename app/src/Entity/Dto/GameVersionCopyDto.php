<?php

declare(strict_types=1);

namespace App\Entity\Dto;

readonly class GameVersionCopyDto
{
    public function __construct(
        public int $id,
        public int $versionId,
        public bool $original,
        public string $language,
        public string $boxType,
        public bool $isBoxRepro,
        public string $casingType,
        public string $supportType,
        public bool $onCompilation,
        public bool $reedition,
        public bool $hasManual,
        public string $status,
        public string $type,
        public string $region,
        public ?string $comments,
        public bool $isROM,
        public string $platformName,
        public string $gameTitle,
        public int $transactionCount,
    ) {
    }
}
