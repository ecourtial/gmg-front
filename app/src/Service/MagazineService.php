<?php
declare(strict_types=1);

namespace App\Service;

class MagazineService extends AbstractService
{
    /** @return array<string, mixed> */
    public function getList(): array
    {
        /** @var array{result: list<array<string, scalar>>, totalResultCount: int} $data */
        $data = $this->clientFactory
            ->getAnonymousClient()
            ->get('magazines?orderBy[]=title-asc&limit='.self::MAX_RESULT_COUNT);

        return $data;
    }

    /** @return array<string, mixed> */
    public function getByIds(array $magazinesIds): array
    {
        // if (empty($versionsIds)) return ['result' => []];
        $magazinesFilter = '';
        foreach ($magazinesIds as $versionId) {
            $magazinesFilter .= "&id[]=".$versionId;
        }

        /** @var array{result: list<array<string, scalar>>, totalResultCount: int} $data */
        $data = $this->clientFactory
            ->getAnonymousClient()
            ->get("magazines?orderBy[]=id-asc&limit=".self::MAX_RESULT_COUNT.$magazinesFilter);

        return $data;
    }

    protected function getResourceType(): string
    {
        return 'magazine';
    }
}
