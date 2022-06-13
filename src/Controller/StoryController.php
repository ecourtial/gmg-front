<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\StoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class StoryController extends AbstractController
{
    public function __construct(
        private readonly StoryService $service,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/stories', methods: ['GET'], name: 'story_list')]
    public function getList(): Response
    {
        $data = $this->service->getList();

        return $this->render(
            'story/list.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'stories_title',
                        ['%count%' => $data['totalResultCount']]
                    ),
                'screenDescription' => $this->translator->trans('stories_description'),
                'stories' => $data['stories']
            ]
        );
    }
}
