<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\GameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;

class GameController extends AbstractController
{
    public function __construct(
        private readonly GameService $service,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route('/game/{id<\d+>}', methods: ['GET'], name: 'version_details')]
    public function versionDetails(int $id): Response
    {
        $version = $this->service->getVersionById($id);

        return $this->render(
            'game/version-details.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'game.version_details',
                        [
                            '%title%' => $version['gameTitle'],
                            '%platform%' => $version['platformName']
                        ]),
                'version' => $version
            ]);
    }

    #[Route('/games', methods: ['GET'], name: 'games_filtered_list')]
    public function filteredList(Request $request): Response
    {
        $filter = $this->request->get('filter');

        if (false === \array_key_exists($filter, GameService::FILTERS)) {
            throw new NotFoundHttpException();
        }

        return $this->render(
            'game/standard-list.html.twig',
            [
                'screenTitle' => $this->translator->trans(GameService::FILTERS[$filter]),
                'data' => $this->service->getFilteredList($filter)
            ]);
    }
}
