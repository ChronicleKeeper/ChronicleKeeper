<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Presentation\Controller;

use ChronicleKeeper\Chat\Infrastructure\Repository\ConversationFileStorage;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/chat-reset', name: 'chat_reset')]
class ResetConversation extends AbstractController
{
    use HandleFlashMessages;

    public function __construct(
        private readonly ConversationFileStorage $storage,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->storage->resetTemporary();

        return $this->redirectToRoute('chat');
    }
}
