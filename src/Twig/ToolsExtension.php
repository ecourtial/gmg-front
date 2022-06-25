<?php

declare(strict_types=1);

namespace App\Twig;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ToolsExtension extends AbstractExtension
{
    public const MONTHS = [
        1 => 'month.january',
        2 => 'month.february',
        3 => 'month.march',
        4 => 'month.april',
        5 => 'month.may',
        6 => 'month.june',
        7 => 'month.july',
        8 => 'month.august',
        9 => 'month.september',
        10 => 'month.october',
        11 => 'month.november',
        12 => 'month.december',
    ];

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }


    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_yes_no_key', [$this, 'getYesNoKey']),
            new TwigFunction('get_month_label', [$this, 'getMonthLabel']),
        ];
    }

    public function getYesNoKey(bool $status): string
    {
        if ($status) {
            return $this->translator->trans('see.yes');
        }

        return $this->translator->trans('see.no');
    }

    public function getMonthLabel(int $monthId): string
    {
        return $this->translator->trans(self::MONTHS[$monthId]);
    }
}
