<?php
declare(strict_types=1);

namespace App\Entity\Dto;

readonly class GameVersionDto
{
    public function __construct(
        public int $id,
        public int $platformId,
        public int $gameId,
        public int $releaseYear,
        public bool $todoSoloSometimes,
        public bool $todoMultiplayerSometimes,
        public bool $singleplayerRecurring,
        public bool $multiplayerRecurring,
        public bool $toDo,
        public bool $toBuy,
        public bool $toWatchBackground,
        public bool $toWatchSerious,
        public bool $toRewatch,
        public bool $topGame,
        public bool $hallOfFame,
        public int $hallOfFameYear,
        public int $hallOfFamePosition,
        public bool $playedItOften,
        public bool $ongoing,
        public ?string $comments = null,
        public bool $todoWithHelp,
        public bool $bestGameForever,
        public int $toWatchPosition,
        public int $toDoPosition,
        public bool $finished,
        public string $platformName,
        public string $gameTitle,
        public int $storyCount,
        public int $copyCount,
    ) {}
}
