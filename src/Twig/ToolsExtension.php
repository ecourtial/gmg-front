<?php

declare(strict_types=1);

namespace App\Twig;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ToolsExtension extends AbstractExtension
{
    private const MONTHS = [
        'month.january',
        'month.february',
        'month.march',
        'month.april',
        'month.may',
        'month.june',
        'month.july',
        'month.august',
        'month.september',
        'month.october',
        'month.november',
        'month.december',
    ];

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }


    public function getFunctions()
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
