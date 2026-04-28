<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Controller;

use App\Service\HomeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class HomeController extends AbstractController
{
    public function __construct(private readonly HomeService $service, private readonly TranslatorInterface $translator)
    {
    }

    #[Route('/', name: 'homepage', methods: ['GET'])]
    public function __invoke(): Response
    {
        $homeData = $this->service->getHomeData();

        return $this->render(
            'home/body.html.twig',
            [
                'screenTitle' => $this->translator->trans('home_title'),
                'data' => $homeData,
                'copiesDistributionStats' => \json_encode($homeData['versionsData']),
                'copiesDistributionNotOnCompilationStats' => \json_encode($homeData['copiesDistributionNotOnCompilationStats']),
            ]
        );
    }
}
