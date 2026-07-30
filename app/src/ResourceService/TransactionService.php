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
    /**
     * @return ResourceCollectionResponseDto<TransactionDto>
     */
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
            (int) $data['id'],
            (int)  $data['versionId'],
            (int) $data['year'],
            (int) $data['month'],
            (int) $data['day'],
            (string) $data['type'],
            (string) $data['platformName'],
            (string) $data['gameTitle'],
            isset($data['copyId']) ? (int) $data['copyId'] : null,
            (string) $data['notes'],
        );
    }
}
