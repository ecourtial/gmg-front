<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\GameService;
use App\Service\PlatformService;
use App\Service\VersionService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class VersionController extends AbstractController
{
    public function __construct(
        private readonly VersionService $service,
        private readonly TranslatorInterface $translator,
        private readonly GameService $gameService,
        private readonly PlatformService $platformService,
    ) {
    }

    #[Route('/version/{id<\d+>}', methods: ['GET'], name: 'version_details')]
    public function versionDetails(int $id): Response
    {
        $version = $this->service->getById($id);

        return $this->render(
            'version/version-details.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'game.version_details',
                        [
                            '%title%' => $version['gameTitle'],
                            '%platform%' => $version['platformName']
                        ]
                    ),
                'version' => $version
            ]
        );
    }

    #[Route('/games/filtered/{filter<\w+>}', methods: ['GET'], name: 'versions_filtered_list')]
    public function filteredList(string $filter): Response
    {
        if (false === \array_key_exists($filter, VersionService::FILTERS)) {
            throw new NotFoundHttpException();
        }

        $data = $this->service->getFilteredList($filter);

        return $this->render(
            'version/standard-list.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        VersionService::FILTERS[$filter]['title'],
                        ['%count%' => $data['totalResultCount']]
                    ),
                'screenSubTitle' => $this->translator
                    ->trans('have_copy_for_x_of_them', ['%count%' => $data['ownedCount']]),
                'screenDescription' => $this->translator
                    ->trans(VersionService::FILTERS[$filter]['description']),
                'versions' => $data['result']
            ]
        );
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
            'version/version-details.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'game.version_details',
                        [
                            '%title%' => $version['gameTitle'],
                            '%platform%' => $version['platformName']
                        ]
                    ),
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
            'version/standard-list.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'originals.title',
                        ['%count%' => $data['totalResultCount']]
                    ),
                'screenDescription' => $this->translator
                    ->trans('originals.description'),
                'versions' => $data['result']
            ]
        );
    }

    #[Route('/game/with-priority/{filter<\w+>}', methods: ['GET'], name: 'versions_with_priority')]
    public function getListWithPriority(string $filter): Response
    {
        if (false === \array_key_exists($filter, VersionService::FILTERS_WITH_PRIORITY)) {
            throw new NotFoundHttpException();
        }

        $data = $this->service->getFilteredListWithPrio($filter);

        return $this->render(
            'version/list-with-priority.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        VersionService::FILTERS_WITH_PRIORITY[$filter]['title'],
                        ['%count%' => \count($data['withPriority']) + \count($data['withoutPriority'])]
                    ),
                'screenSubTitle' => $this->translator
                    ->trans('have_copy_for_x_of_them', ['%count%' => $data['ownedCount']]),
                'screenDescription' => $this->translator
                    ->trans(VersionService::FILTERS_WITH_PRIORITY[$filter]['description']),
                'data' => $data
            ]
        );
    }

    #[Route('/games/search', methods: ['POST'], name: 'version_search')]
    public function search(Request $request): Response
    {
        $query = \trim($request->get('query', ''));
        if ($query !== '') {
            $data = $this->service->search($query);
        } else {
            $data = ['result' => [], 'totalResultCount' => 0];
        };

        $params = [
            'screenTitle' => $this->translator
                ->trans(
                    'search_results',
                    ['%count%' => $data['totalResultCount']]
                ),
            'screenSubTitle' => $this->translator->trans(
                'search_results_subtitle',
                ['%query%' => $query]
            ),
            'versions' => $data['result']
        ];

        if ($data['totalResultCount'] > 0) {
            $params['screenDescription'] = $this->translator
                ->trans('have_copy_for_x_of_them', ['%count%' => $data['ownedCount']]);
        }

        return $this->render(
            'version/standard-list.html.twig',
            $params
        );
    }

    #[Route('/version/add', methods: ['GET', 'POST'], name: 'add_version'), IsGranted('ROLE_USER')]
    public function add(Request $request): Response
    {
        if ($request->getMethod() === 'GET') {
            return $this->render(
                'version/form.html.twig',
                [
                    'screenTitle' => $this->translator->trans('menu.add_version'),
                    'games' => $this->gameService->getList()['result'],
                    'platforms' => $this->platformService->getList()['result'],
                    'selectedPlatform' => $request->get('platform', 0),
                    'selectedGame' => $request->get('game', 0),
                ]
            );
        }

        if (false === $this->isCsrfTokenValid('add_version', $request->get('_csrf_token'))) {
            $request->getSession()->getFlashBag()->add('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('add_version');
        }

        $payload = $request->request->all();
        unset($payload['_csrf_token']);

        $id = $this->service->add($payload)['id'];

        return $this->redirectToRoute('version_details', ['id' => $id]);
    }

    #[Route('/version/edit/{id<\d+>}', methods: ['GET', 'POST'], name: 'edit_version'), IsGranted('ROLE_USER')]
    public function edit(Request $request, int $id): Response
    {
        $version = $this->service->getById($id);

        if ($request->getMethod() === 'GET') {
            return $this->render(
                'version/form.html.twig',
                [
                    'screenTitle' => $this->translator->trans('menu.add_version'),
                    'games' => $this->gameService->getList()['result'],
                    'platforms' => $this->platformService->getList()['result'],
                    'selectedPlatform' => $version['platformId'],
                    'selectedGame' => $version['gameId'],
                    'version' => $version,
                ]
            );
        }

        if (false === $this->isCsrfTokenValid('add_version', $request->get('_csrf_token'))) {
            $request->getSession()->getFlashBag()->add('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('edit_version', ['id' => $id]);
        }

        $payload = $request->request->all();
        unset($payload['_csrf_token']);

        $this->service->update($id, $payload)['id'];

        return $this->redirectToRoute('version_details', ['id' => $id]);
    }
}
