<?php

declare(strict_types=1);

namespace App\Twig;

use App\Service\NoteService;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NoteExtension extends AbstractExtension
{
    public function __construct(private readonly NoteService $noteService)
    {
    }


    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_notes_count', [$this, 'getCount']),
        ];
    }

    public function getCount(): int
    {
        return $this->noteService->getTotalCount();
    }
}
