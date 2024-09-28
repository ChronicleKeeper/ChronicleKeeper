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
    '/',
    name: 'home',
    defaults: ['conversation' => null],
)]
#[Route(
    '/chat/{conversation}',
    name: 'chat',
    defaults: ['conversation' => null],
)]
class Chat extends AbstractController
{
    use HandleFlashMessages;

    public function __construct(
        private readonly Environment $environment,
        private readonly ConversationFileStorage $storage,
    ) {
    }

    public function __invoke(Request $request, string|null $conversation): Response
    {
        if ($conversation !== null) {
            $conversation = $this->storage->load($conversation);
            if ($conversation === null) {
                $this->addFlashMessage($request, Alert::DANGER, 'Das gesuchte Gespräch ist nicht vorhanden.');

                return $this->redirectToRoute('chat');
            }

            $request->getSession()->set('last_conversation', $conversation->id);
        }

        return new Response($this->environment->render(
            'chat/chat.html.twig',
            ['conversation' => $conversation],
        ));
    }
}
