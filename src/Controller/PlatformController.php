<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\PlatformService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlatformController extends AbstractController
{
    public function __construct(
        private readonly PlatformService $service,
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
                'screenTitle' => $this->translator->trans('menu.platforms') . " ({$data['totalResultCount']})",
                'data' => $data
            ]);
    }
}
