<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class BadgeExtension extends AbstractExtension
{
    private const VERSIONS_BADGES = [
        'bestGameForever' => ['img' => 'diamond', 'alt' => 'best_game_forever'],
        'hallOfFame' => ['img' => 'hall-of-fame', 'alt' => 'in_the_hall_of_fame'],
        'todoWithHelp' => ['img' => 'help', 'alt' => 'to_do_with_help'],
        'playedItOften' => ['img' => 'time', 'alt' => 'played_it_a_lot'],
        'toBuy' => ['img' => 'to-buy', 'alt' => 'to_buy'],
        'topGame' => ['img' => 'top', 'alt' => 'top_game'],
    ];

    public function getFunctions()
    {
        return [
            new TwigFunction('get_badges_for_version', [$this, 'getBadgesForVersion']),
        ];
    }

    public function getBadgesForVersion(array $version): array
    {
        $badges = [];

        foreach (self::VERSIONS_BADGES as $key => $attributes) {
            if ((int)$version[$key] === 1) {
                $badges[$attributes['img']] = $attributes['alt'];
            }
        }

        return $badges;
    }
}