<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\GameService;
use App\Service\PlatformService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlatformController extends AbstractController
{
    public function __construct(
        private readonly PlatformService $service,
        private readonly GameService $gameService,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route('/platforms', methods: ['GET'], name: 'platforms_list')]
    public function __invoke(): Response
    {
        $data = $this->service->getList();

        return $this->render(
            'platform/list.html.twig',
            [
                'screenTitle' => $this->translator->trans('platforms.title', ['%count%' => $data['totalResultCount']]),
                'data' => $data
            ]);
    }

    #[Route('/platform/{id<\d+>}', methods: ['GET'], name: 'games_per_platform')]
    public function perPlatformList(int $id): Response
    {
        $data = $this->gameService->getByPlatform($id);
        $games = $data['games'];
        $platform = $data['platform'];

        $count = 0;
        foreach ($games['result'] as $game) {
            if ((int)$game['copyCount'] > 0) {
                $count++;
            }
        }

        return $this->render(
            'game/standard-list.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'games_for_platform_title',
                        [
                            '%name%' => $platform['name'],
                            '%count%' => $games['totalResultCount']
                        ]),
                'screenSubTitle' => $this->translator
                    ->trans('have_copy_for_x_of_them', ['%count%' => $count]),
                'games' => $games['result']
            ]);
    }
}
