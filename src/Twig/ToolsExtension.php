<?php

declare(strict_types=1);

namespace App\Twig;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ToolsExtension extends AbstractExtension
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }


    public function getFunctions()
    {
        return [
            new TwigFunction('get_yes_no_key', [$this, 'getYesNoKey']),
        ];
    }

    public function getYesNoKey(bool $status): string
    {
        if ($status) {
            return $this->translator->trans('see.yes');
        }

        return $this->translator->trans('see.no');
    }
}
