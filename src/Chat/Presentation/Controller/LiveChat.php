<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Presentation\Controller;

use ChronicleKeeper\Chat\Infrastructure\Repository\ConversationFileStorage;
use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[Route(
    '/live-chat/{conversationId}',
    name: 'chat_live',
    defaults: ['conversationId' => null],
)]
class LiveChat extends AbstractController
{
    use HandleFlashMessages;

    public function __construct(
        private readonly Environment $environment,
        private readonly ConversationFileStorage $storage,
    ) {
    }

    public function __invoke(Request $request, string|null $conversationId): Response
    {
        $conversation = null;
        if ($conversationId !== null) {
            $conversation = $this->storage->load($conversationId);
            if ($conversation === null) {
                $this->addFlashMessage($request, Alert::DANGER, 'Das gesuchte Gespräch ist nicht vorhanden.');

                return $this->redirectToRoute('chat_live');
            }
        }

        return new Response($this->environment->render(
            'chat/live-chat.html.twig',
            ['conversation' => $conversation],
        ));
    }
}
