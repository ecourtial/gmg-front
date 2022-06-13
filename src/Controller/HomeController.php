<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Controller;

use App\Service\HomeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class HomeController extends AbstractController
{
    #[Route('/', methods: ['GET'], name: 'homepage')]
    public function __invoke(HomeService $service, TranslatorInterface $translator): Response
    {
        return $this->render(
            'home/body.html.twig',
            [
                'screenTitle' => $translator->trans('menu.home'),
                'data' => $service->getHomeData()
            ]
        );
    }
}
