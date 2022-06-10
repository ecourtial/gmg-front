<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CopyService;
use App\Service\VersionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class CopyController extends AbstractController
{
    public function __construct(
        private readonly CopyService $service,
        private readonly VersionService $versionService,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route('/copies/version/{versionId<\d+>}', methods: ['GET'], name: 'copies_per_version')]
    public function perVersion(int $versionId): Response
    {
        $version = $this->versionService->getVersionById($versionId);
        $copies = $this->service->getByVersion($versionId);

        return $this->render(
            'copy/list.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'copies_for_version_title',
                        [
                            '%title%' => $version['gameTitle'],
                            '%platform%' => $version['platformName'],
                            '%count%' => $copies['totalResultCount']
                        ]),
                'copies' => $copies['result']
            ]);
    }
}
