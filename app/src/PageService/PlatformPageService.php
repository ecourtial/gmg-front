<?php
declare(strict_types=1);

namespace App\PageService;

use App\Entity\Dto\Specific\PlatformPageDetailsDto;
use App\ResourceService\PlatformService;
use App\ResourceService\VersionService;

readonly class PlatformPageService
{
    public function __construct(
        private PlatformService $platformService,
        private VersionService  $versionService,
    ) {}

    public function getPlatformPageDetails(int $platformId): PlatformPageDetailsDto
    {
        return new PlatformPageDetailsDto(
            $this->versionService->getByPlatform($platformId),
            $this->platformService->getById($platformId)
        );
    }
}
