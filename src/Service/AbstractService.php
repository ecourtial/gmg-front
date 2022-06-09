<?php

declare(strict_types=1);

namespace App\Service;

use App\Client\ClientFactory;

abstract class AbstractService
{
    protected CONST MAX_RESULT_COUNT = 500;

    public function __construct(protected readonly ClientFactory $clientFactory)
    {
    }
}