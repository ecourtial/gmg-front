<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class BadgeExtension extends AbstractExtension
{
    private const VERSIONS_BADGES = [
        'bestGameForever' => ['img' => 'diamond', 'title' => 'best_game_forever'],
        'hallOfFame' => ['img' => 'hall-of-fame', 'title' => 'in_the_hall_of_fame'],
        'todoWithHelp' => ['img' => 'help', 'title' => 'to_do_with_help'],
        'playedItOften' => ['img' => 'time', 'title' => 'played_it_a_lot'],
        'toBuy' => ['img' => 'to-buy', 'title' => 'to_buy'],
        'topGame' => ['img' => 'top', 'title' => 'top_game'],
    ];

    private const OWNERSHIP_BADGE = [
        'no' => ['img' => 'no', 'title' => 'have_no_copy'],
        'yes' => ['img' => 'check', 'title' => 'have_at_least_one_copy'],
    ];

    public function getFunctions()
    {
        return [
            new TwigFunction('get_ownership_badge', [$this, 'getOwnershipBadge']),
            new TwigFunction('get_badges_for_version', [$this, 'getBadgesForVersion']),
        ];
    }

    public function getOwnershipBadge(array $version): array
    {
        $key = 'no';

        if ((int)$version['copyCount'] > 0) {
            $key = 'yes';
        }

        return [self::OWNERSHIP_BADGE[$key]['img'] => self::OWNERSHIP_BADGE[$key]['title']];
    }


    public function getBadgesForVersion(array $version, bool $setOwnershipBadge = false): array
    {
        $badges = [];
        $ownerShipBadge = [];

        foreach (self::VERSIONS_BADGES as $key => $attributes) {
            if ((int)$version[$key] === 1) {
                $badges[$attributes['img']] = $attributes['title'];
            }
        }

        if ($setOwnershipBadge) {
            $key = 'no';

            if ((int)$version['copyCount'] > 0) {
                $key = 'yes';
            }

            $ownerShipBadge = [self::OWNERSHIP_BADGE[$key]['img'] => self::OWNERSHIP_BADGE[$key]['title']];
        }

        return \array_merge($ownerShipBadge, $badges);
    }
}
