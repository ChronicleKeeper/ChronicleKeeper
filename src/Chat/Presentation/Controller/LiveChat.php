<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Presentation\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[Route('/live-chat', name: 'chat_live', methods: [Request::METHOD_GET, Request::METHOD_POST])]
class LiveChat extends AbstractController
{
    public function __construct(
        private readonly Environment $environment,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        return new Response($this->environment->render('chat/live-chat.html.twig'));
    }
}
