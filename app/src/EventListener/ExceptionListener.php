<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Api\Enum\ApiResponseCode;
use App\Exception\GenericApiException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): ?RedirectResponse
    {
        // You get the exception object from the received event
        $exception = $event->getThrowable();
        if ($exception instanceof GenericApiException) {
            if (ApiResponseCode::SECURITY_ERROR_CODE->value === $exception->getApiReturnCode()) {
                return new RedirectResponse('security_login');
            }

            exit($exception->getMessage());
        }

        return null;
    }
}
