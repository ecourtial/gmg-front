<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\GameService;
use App\Service\VersionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class GameController extends AbstractController
{
    public function __construct(
        private readonly GameService $service,
        private readonly VersionService $versionService,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route('/games', methods: ['GET'], name: 'games_list')]
    public function list(): Response
    {
        $data = $this->service->getList();

        return $this->render(
            'game/list.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'games_list_title',
                        ['%count%' => $data['totalResultCount']]),
                'screenSubTitle' => $this->translator
                    ->trans('games_list_subtitle', ['%count%' => $data['versionCount']]),
                'games' => $data['result']
            ]);
    }

    #[Route('/game/{id<\d+>}', methods: ['GET'], name: 'game_details')]
    public function get(int $id): Response
    {
        $data = $this->versionService->getByGame($id);
        $versions = $data['versions'];
        $game = $data['game'];

        return $this->render(
            'version/standard-list.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'game_versions_list', ['%title%' => $game['title']]),
                'screenSubTitle' => $this->translator
                    ->trans(
                        'games_versions_subtitle',
                        [
                            '%resultCount%' => $versions['totalResultCount'],
                            '%copyCount%' => $data['ownedCount'],
                        ]
                    ),
                'versions' => $versions['result']
            ]);
    }
}
