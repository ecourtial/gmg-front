<?php

namespace App\EventListener;

use App\Exception\GenericApiException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        // You get the exception object from the received event
        $exception = $event->getThrowable();
        if ($exception instanceof GenericApiException) {
            if ($exception->getApiReturnCode() === 13) {
                return new RedirectResponse('security_login');
            }

            die($exception->getMessage());
        }
    }
}
