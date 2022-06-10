<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\GameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class GameController extends AbstractController
{
    public function __construct(
        private readonly GameService $service,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route('/games', methods: ['GET'], name: 'games_list')]
    public function list(int $id): Response
    {
        $data = $this->service->getList($id);
        $games = $data['games'];
        $platform = $data['platform'];

        return $this->render(
            'version/standard-list.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'games_list_title',
                        ['%count%' => $games['totalResultCount']]),
                'screenSubTitle' => $this->translator
                    ->trans('games_list_subtitle'),
                'games' => $games['result']
            ]);
    }
}
