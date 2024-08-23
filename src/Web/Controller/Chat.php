<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[Route('/', name: 'chat')]
class Chat
{
    public function __construct(
        private Environment $environment,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        return new Response($this->environment->render('chat.html.twig', ['communicationBag' => []]));
    }
}
