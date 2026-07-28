<?php

declare(strict_types=1);

namespace App\Entity\Dto\Specific;

use App\Entity\Dto\PlatformDto;

readonly class PlatformPageDetailsDto
{
    public function __construct(
        public VersionsDataDto $versionsData,
        public PlatformDto $platform
    ) {}
}
