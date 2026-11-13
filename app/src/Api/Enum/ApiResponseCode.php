<?php

declare(strict_types=1);

namespace App\Api\Enum;

enum ApiResponseCode: int
{
    case USER_NOT_FOUND_API_CODE = 1;
    case BAD_CREDENTIALS_API_CODE = 2;
    case USER_ACCOUNT_DISABLED_API_CODE = 3;
    case RESOURCE_HAS_LINKED_RESOURCES = 9;
    case SECURITY_ERROR_CODE = 13;
}
