<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\Component\Asset\Packages;

class BadgeExtension extends AbstractExtension
{
    public function __construct(private readonly Packages $packages)
    {
    }

    private const VERSIONS_BADGES = [
        'bestGameForever' => ['img' => 'diamond', 'title' => 'best_game_forever'],
        'hallOfFame' => ['img' => 'hall-of-fame', 'title' => 'in_the_hall_of_fame'],
        'todoWithHelp' => ['img' => 'help', 'title' => 'to_do_with_help'],
        'playedItOften' => ['img' => 'time', 'title' => 'played_it_a_lot'],
        'toBuy' => ['img' => 'to-buy', 'title' => 'to_buy'],
        'topGame' => ['img' => 'top', 'title' => 'top_game'],
    ];

    private const OWNERSHIP_BADGES = [
        'no' => ['img' => 'no', 'title' => 'have_no_copy'],
        'yes' => ['img' => 'check', 'title' => 'have_at_least_one_copy'],
    ];

    private const STORIES_BADGES = [
        'watched' => ['img' => 'eye', 'title' => 'entity.watched_it'],
        'played' => ['img' => 'controller', 'title' => 'entity.played_at_it'],
    ];

    private const TRANSACTIONS_BADGES = [
        'in' => ['img' => 'in', 'title' => 'entity.transaction_in'],
        'out' => ['img' => 'out', 'title' => 'entity.transaction_out'],
    ];

    private const TRANSACTIONS_IN_BADGE = ['Bought', 'Loan-out-return', 'Loan-in'];

    public function getFunctions()
    {
        return [
            new TwigFunction('get_ownership_badge', [$this, 'getOwnershipBadge']),
            new TwigFunction('get_badges_for_version', [$this, 'getBadgesForVersion']),
            new TwigFunction('get_status_badge', [$this, 'getStatusBadge']),
            new TwigFunction('get_story_badges', [$this, 'getStoryBadges']),
            new TwigFunction('get_transaction_type_badge', [$this, 'getTransactionTypeBadge']),
        ];
    }

    public function getOwnershipBadge(array $version): array
    {
        $key = 'no';

        if ((int)$version['copyCount'] > 0) {
            $key = 'yes';
        }

        return [self::OWNERSHIP_BADGES[$key]['img'] => self::OWNERSHIP_BADGES[$key]['title']];
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

            $ownerShipBadge = [self::OWNERSHIP_BADGES[$key]['img'] => self::OWNERSHIP_BADGES[$key]['title']];
        }

        return \array_merge($ownerShipBadge, $badges);
    }

    public function getStatusBadge(bool $status): string
    {
        $key = 'no';

        if ($status) {
            $key = 'yes';
        }

        $img = self::OWNERSHIP_BADGES[$key]['img'];

        return $this->packages->getUrl("assets/img/badges/{$img}.png");
    }

    public function getStoryBadges(array $story): array
    {
        $badges = [];

        foreach (self::STORIES_BADGES as $key => $attributes) {
            if ((int)$story[$key] === 1) {
                $badges[$attributes['img']] = $attributes['title'];
            }
        }

        return $badges;
    }

    public function getTransactionTypeBadge(string $transactionType): string
    {
        $key = 'out';

        if (\in_array($transactionType, self::TRANSACTIONS_IN_BADGE)) {
            $key = 'in';
        }

        $img = self::TRANSACTIONS_BADGES[$key]['img'];

        return $this->packages->getUrl("assets/img/badges/{$img}.png");
    }

}
