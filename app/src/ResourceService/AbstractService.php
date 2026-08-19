<?php

declare(strict_types=1);

namespace App\ResourceService;

use App\Api\Client\ClientFactory;
use App\Api\RawCollectionResourceApiResponseDto;
use App\Api\RawSingleResourceApiResponseDto;
use App\Api\ResourceCollectionResponseDto;

/**
 * @template TDto of object
 */
abstract class AbstractService
{
    protected const int MAX_RESULT_COUNT = 1000;

    public function __construct(private readonly ClientFactory $clientFactory)
    {
    }

    /**
     * @return TDto
     */
    public function getById(int $entityId): object
    {
        return $this->hydrateObject(
            $this->formatSingleResourceApiResponseDto(
                $this->clientFactory
                    ->getAnonymousClient()
                    ->get("{$this->getResourceNamePlural()}/{$entityId}")
            )
        );
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return TDto
     */
    public function add(array $data): object
    {
        return $this->hydrateObject(
            $this->formatSingleResourceApiResponseDto(
                $this->clientFactory->getAuthenticatedClient()->post(
                    $this->getResourceNamePlural(),
                    [],
                    $data
                )
            )
        );
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return TDto
     */
    public function update(int $entityId, array $data): object
    {
        return $this->hydrateObject(
            $this->formatSingleResourceApiResponseDto(
                $this->clientFactory->getAuthenticatedClient()->patch(
                    "{$this->getResourceNamePlural()}/{$entityId}",
                    [],
                    $data
                )
            )
        );
    }

    public function delete(int $entityId): void
    {
        $this->clientFactory->getAuthenticatedClient()->delete($this->getResourceNamePlural().'/'.$entityId);
    }

    /** @param array<string, mixed>  $data */
    protected function formatSingleResourceApiResponseDto(array $data): RawSingleResourceApiResponseDto
    {
        foreach ($data as $key => $value) {
            if (
                false === is_bool($value)
                && false === is_numeric($value)
                && false === is_string($value)
                && false === is_null($value)
            ) {
                throw new \InvalidArgumentException('Impossible to format single resource response in '.static::class.' because the value for the key '.$key.' is not supported!');
            }
        }

        return new RawSingleResourceApiResponseDto($data);
    }

    /** @param array<string, mixed>  $data */
    protected function formatResourceCollectionApiResponseDto(array $data): RawCollectionResourceApiResponseDto
    {
        $keys = ['resultCount', 'totalResultCount', 'page', 'totalPageCount'];

        foreach ($keys as $key) {
            if (
                false === array_key_exists($key, $data)
                || (
                    false === is_bool($data[$key])
                    && false === is_numeric($data[$key])
                    && false === is_string($data[$key])
                )
            ) {
                throw new \InvalidArgumentException('Impossible to format resource collection response in '.static::class.' because the value for the key '.$key.' is not supported!');
            }
        }

        if (false === array_key_exists('result', $data) || false === is_array($data['result'])) {
            throw new \InvalidArgumentException('Impossible to format resource collection response in '.static::class.' because the value for the result of the collection '.$key.' is not supported!');
        }

        $singleResourceCollection = [];
        foreach ($data['result'] as $singleResult) {
            /** @var array<string, mixed>  $singleResult */
            $singleResourceCollection[] = $this->formatSingleResourceApiResponseDto($singleResult);
        }

        return new RawCollectionResourceApiResponseDto(
            (int) $data['resultCount'],
            (int) $data['totalResultCount'],
            (int) $data['page'],
            (int) $data['totalPageCount'],
            $singleResourceCollection,
        );
    }

    /**
     * @return ResourceCollectionResponseDto<TDto>
     */
    protected function getCollection(string $query, bool $authenticated = false): ResourceCollectionResponseDto
    {
        if (true === $authenticated) {
            $client = $this->clientFactory->getAuthenticatedClient();
        } else {
            $client = $this->clientFactory->getAnonymousClient();
        }

        return $this->hydrateResultCollection(
            $this->formatResourceCollectionApiResponseDto(
                $client->get($this->getResourceNamePlural().'?'.$query)
            )
        );
    }

    /**
     * @return ResourceCollectionResponseDto<TDto>
     */
    private function hydrateResultCollection(RawCollectionResourceApiResponseDto $data): ResourceCollectionResponseDto
    {
        $resources = [];

        foreach ($data->results as $item) {
            $resources[] = $this->hydrateObject($item);
        }

        return new ResourceCollectionResponseDto(
            $data->resultCount,
            $data->totalResultCount,
            $data->page,
            $data->totalPageCount,
            $resources,
        );
    }

    abstract protected function getResourceNamePlural(): string;

    /**
     * @return TDto
     */
    abstract protected function hydrateObject(RawSingleResourceApiResponseDto $dto): object;
}
