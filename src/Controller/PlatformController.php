<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\VersionService;
use App\Service\PlatformService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlatformController extends AbstractController
{
    public function __construct(
        private readonly PlatformService $service,
        private readonly VersionService $versionService,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route('/platforms', methods: ['GET'], name: 'platforms_list')]
    public function list(): Response
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
        $data = $this->versionService->getByPlatform($id);
        $versions = $data['versions'];
        $platform = $data['platform'];

        return $this->render(
            'version/standard-list.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'games_for_platform_title',
                        [
                            '%name%' => $platform['name'],
                            '%count%' => $versions['totalResultCount']
                        ]),
                'screenSubTitle' => $this->translator
                    ->trans('have_copy_for_x_of_them', ['%count%' => $data['ownedCount']]),
                'versions' => $versions['result']
            ]);
    }
}
