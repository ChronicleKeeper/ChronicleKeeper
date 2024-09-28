<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Presentation\Controller;

use ChronicleKeeper\Chat\Infrastructure\Repository\ConversationFileStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

use function dump;

#[Route(
    '/live-chat/{conversationId}',
    name: 'chat_live',
    defaults: ['conversationId' => null],
    methods: [Request::METHOD_GET],
)]
class LiveChat extends AbstractController
{
    public function __construct(
        private readonly Environment $environment,
        private readonly ConversationFileStorage $storage,
    ) {
    }

    public function __invoke(Request $request, string|null $conversationId): Response
    {
        if ($conversationId !== null) {
            dump('LOAD EXISTING!');
            exit;
        }

        $conversation = $this->storage->loadTemporary();

        return new Response($this->environment->render(
            'chat/live-chat.html.twig',
            ['conversation' => $conversation],
        ));
    }
}
