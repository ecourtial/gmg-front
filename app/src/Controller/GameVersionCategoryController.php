<?php

declare(strict_types=1);

namespace App\Controller;

use App\Api\Enum\ApiResponseCode;
use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\Specific\GamesDataDto;
use App\Exception\GenericApiException;
use App\PageService\GamePageService;
use App\PageService\GameVersionCategoryPageService;
use App\ResourceService\GameService;
use App\ResourceService\GameVersionCategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class GameVersionCategoryController extends AbstractController
{
    public function __construct(
        private readonly GameVersionCategoryService $gameVersionCategoryService,
        private readonly GameVersionCategoryPageService $gameVersionCategoryPageService,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/game-version-categories', name: 'game_version_categories_list', methods: ['GET'])]
    public function list(): Response
    {
        $data = $this->gameVersionCategoryService->getList();

        return $this->render(
            'game-version-category/list.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'categories_list',
                        ['%count%' => $data->totalResultCount]
                    ),
                'categories' => $data->result,
            ]
        );
    }

    #[Route('/game-version-category/{id<\d+>}', name: 'game_version_category_details', methods: ['GET'])]
    public function get(int $id): Response
    {
        $categoryDetails = $this->gameVersionCategoryPageService->getCategoryDetails($id);

        return $this->render(
            'game-version-category/details.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'game_versions_list',
                        ['%title%' => $categoryDetails->category->name],
                    ),
                'screenSubTitle' => $this->translator
                    ->trans(
                        'category_details',
                        [
                            '%count%' => $categoryDetails->category->versionCount,
                        ]
                    ),
                'category' => $categoryDetails->category,
                'versions' => $categoryDetails->versions->result,
            ]
        );
    }

    #[Route('/game-version-category/add', name: 'add_game_version_category', methods: ['GET', 'POST']), IsGranted('ROLE_USER')]
    public function add(Request $request): Response
    {
        if ('GET' === $request->getMethod()) {
            return $this->render(
                'game-version-category/form.html.twig',
                ['screenTitle' => $this->translator->trans('add_category_title')]
            );
        }

        if (false === $this->isCsrfTokenValid('add_game_version_category', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('add_game_version_category');
        }

        $payload = $request->request->all();
        unset($payload['_csrf_token']);
        $id = $this->gameVersionCategoryService->add($payload)->id;

        return $this->redirectToRoute('game_version_category_details', ['id' => $id]);
    }
}
