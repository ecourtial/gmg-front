<?php
namespace App\EventListener;

use App\Exception\GenericApiException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        // You get the exception object from the received event
        $exception = $event->getThrowable();
        if ($exception instanceof GenericApiException) {
            die($exception->getMessage());
        }
    }
}
