<?php

declare(strict_types=1);

namespace App\Api;

readonly class RawSingleResourceApiResponseDto
{
    /**
     * @param array<string, string>|array<string, int>|array<string, float>|array<string, bool>|array<string, null> $data
     */
    public function __construct(public array $data)
    {
    }
}
