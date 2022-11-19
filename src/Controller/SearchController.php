<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search', methods: ['POST'], name: 'search')]
    public function search(Request $request): Response
    {
        $query = \trim($request->get('query_type', ''));
        if ('game' === $query) {
            return $this->forward(GameController::class . '::search');
        } elseif('version' === $query) {
            return $this->forward(VersionController::class . '::search');
        } else {
            throw new NotFoundHttpException("Unknown research query type: '$query'");
        };
    }
}
