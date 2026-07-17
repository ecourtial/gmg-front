<?php
declare(strict_types=1);

namespace App\ResourceService;

use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\MagazineDto;

/**
 * @extends AbstractService<MagazineDto>
 */
class MagazineService extends AbstractService
{
    public function getList(): ResourceCollectionResponseDto
    {
        return $this->hydrateResultCollection(
            $this->clientFactory
                ->getAnonymousClient()
                ->get($this->getResourceType() . 's?orderBy[]=title-asc&limit=' . self::MAX_RESULT_COUNT)
        );
    }

    protected function getResourceType(): string
    {
        return 'magazine';
    }

    protected function hydrateObject(array $data): MagazineDto
    {
        return new MagazineDto(
            $data['id'],
            $data['title'],
            $data['notes'],
            $data['issueCount'],
        );
    }
}
