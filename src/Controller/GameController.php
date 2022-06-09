<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\GameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class GameController extends AbstractController
{
    public function __construct(
        private readonly GameService $service,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route('/version/{id<\d+>}', methods: ['GET'], name: 'version_details')]
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
            ]
        );
    }

    #[Route('/games/filtered/{filter<\w+>}', methods: ['GET'], name: 'versions_filtered_list')]
    public function filteredList(string $filter): Response
    {
        if (false === \array_key_exists($filter, GameService::FILTERS)) {
            throw new NotFoundHttpException();
        }

        $data = $this->service->getFilteredList($filter);

        $count = 0;
        foreach ($data['result'] as $game) {
            if ((int)$game['copyCount'] > 0) {
                $count++;
            }
        }

        return $this->render(
            'game/standard-list.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        GameService::FILTERS[$filter]['title'],
                        ['%count%' => $data['totalResultCount']]
                    ),
                'screenSubTitle' => $this->translator
                    ->trans('have_copy_for_x_of_them', ['%count%' => $count]),
                'screenDescription' => $this->translator
                    ->trans(GameService::FILTERS[$filter]['description']),
                'games' => $data['result']
            ]);
    }

    #[Route('/game/random/{filter<\w+>}', methods: ['GET'], name: 'version_random')]
    public function getRandom(string $filter): Response
    {
        $result = $this->service->getRandom($filter);

        if ((int)$result['totalResultCount'] === 0) {
            return $this->render(
                'general/no-result.html.twig',
                [
                    'screenTitle' => $this->translator->trans('no_result')
                ]
            );
        }

        $version = $result['result'][0];

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
            ]
        );
    }

    #[Route('/games/originals', methods: ['GET'], name: 'versions_originals')]
    public function getOriginals(): Response
    {
        $data = $this->service->getOriginals();

        if ((int)$data['totalResultCount'] === 0) {
            return $this->render(
                'general/no-result.html.twig',
                [
                    'screenTitle' => $this->translator->trans('no_result')
                ]
            );
        }

        return $this->render(
            'game/standard-list.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'originals.title',
                        ['%count%' => $data['totalResultCount']]
                    ),
                'screenDescription' => $this->translator
                    ->trans('originals.description'),
                'games' => $data['result']
            ]);
    }

    #[Route('/game/with-priority/{filter<\w+>}', methods: ['GET'], name: 'versions_with_priority')]
    public function getListWithPriority(string $filter): Response
    {
        if (false === \array_key_exists($filter, GameService::FILTERS_WITH_PRIORITY)) {
            throw new NotFoundHttpException();
        }

        $data = $this->service->getFilteredListWithPrio($filter);
        $count = 0;
        foreach ($data as $subset) {
            foreach ($subset as $game) {
                if ((int)$game['copyCount'] > 0) {
                    $count++;
                }
            }
        }

        return $this->render(
            'game/list-with-priority.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        GameService::FILTERS_WITH_PRIORITY[$filter]['title'],
                        ['%count%' => \count($data['withPriority']) + \count($data['withoutPriority'])]
                    ),
                'screenSubTitle' => $this->translator
                    ->trans('have_copy_for_x_of_them', ['%count%' => $count]),
                'screenDescription' => $this->translator
                    ->trans(GameService::FILTERS_WITH_PRIORITY[$filter]['description']),
                'data' => $data
            ]);
    }
}