<?php

declare(strict_types=1);

namespace App\ResourceService;

use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\TransactionDto;

/**
 * @extends AbstractService<TransactionDto>
 */
class TransactionService extends AbstractService
{
    public function getTransactionsData(int $versionId): ResourceCollectionResponseDto
    {
        $versionFilter = '';

        if (0 !== $versionId) {
            $versionFilter = '&versionId[]='.$versionId;
        }

        return $this->getCollection('orderBy[]=year-asc&orderBy[]=month-asc'.$versionFilter.'&orderBy[]=day-asc&limit='.self::MAX_RESULT_COUNT);
    }

    protected function getResourceNamePlural(): string
    {
        return 'transactions';
    }

    protected function hydrateObject(array $data): TransactionDto
    {
        return new TransactionDto(
            $data['id'],
            $data['versionId'],
            $data['copyId'],
            $data['year'],
            $data['month'],
            $data['day'],
            $data['type'],
            $data['notes'],
            $data['platformName'],
            $data['gameTitle'],
        );
    }
}
