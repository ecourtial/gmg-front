<?php

declare(strict_types=1);

namespace App\ResourceService;

use App\Api\ResourceCollectionResponseDto;
use App\Api\RawSingleResourceApiResponseDto;
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

    protected function hydrateObject(RawSingleResourceApiResponseDto $dto): TransactionDto
    {
        return new TransactionDto(
            (int) $dto->data['id'],
            (int)  $dto->data['versionId'],
            (int) $dto->data['year'],
            (int) $dto->data['month'],
            (int) $dto->data['day'],
            (string) $dto->data['type'],
            (string) $dto->data['platformName'],
            (string) $dto->data['gameTitle'],
            isset($dto->data['copyId']) ? (int) $dto->data['copyId'] : null,
            (string) $dto->data['notes'],
        );
    }
}
