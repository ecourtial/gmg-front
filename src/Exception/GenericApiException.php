<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

class GenericApiException extends \Exception
{
    public function __construct(\Throwable $previous)
    {
        $message = "The API return an unexpected HTTP status code: {$previous->getCode()}.";

        if ($previous instanceof HttpExceptionInterface) {
            $content = \json_decode($previous->getResponse()->getContent(false), true);
            if (is_array($content) and array_key_exists('message', $content)) {
                $message .= " The message returned was the following: '{$content['message']}'.";
            }

            $message .= " If you were submitting a form, please just click on the 'Previous' button of your browser.";
        }

        parent::__construct($message, $previous->getCode(), $previous);
    }
}